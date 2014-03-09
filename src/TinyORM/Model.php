<?php

/**
 * Description of Model
 *
 * @author cagatay
 */

namespace TinyORM;

use TinyORM\Database;
use Exception;

abstract class Model {

    protected $table;
    protected $primary_key = "id";
    protected $data;
    protected $changed_items = array();
    protected $fetched = false;
    protected $dontfetch = false;
    protected $cachable_fields = array();

    public function __construct($id = null) {
        if (null != $id) {
            $this->data[$this->primary_key] = $id;
            $this->dontfetch = true;
        }
    }

    public function disableFetch() {
        $this->dontfetch = true;
    }

    public function enableFetch() {
        $this->dontfetch = false;
    }

    public function __set($name, $value) {


        if (!$this->fetched) {
            if ($this->data[$this->primary_key]) {
                $this->loaddata();
            }
        }


        $this->data[$name] = $value;
        $this->changed_items[] = $name;
    }

    private function isValidDateTime($dateTime) {
        if (!is_string($dateTime)) {
            return false;
        }
        if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $dateTime, $matches)) {
            if (checkdate($matches[2], $matches[3], $matches[1])) {
                return true;
            }
        }

        if (preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $dateTime, $matches)) {
            if (checkdate($matches[2], $matches[3], $matches[1])) {
                return true;
            }
        }

        return false;
    }

    public function __get($name) {

        $isCacheable = in_array($name, $this->cachable_fields) && $this->primary_key && $this->data[$this->primary_key];

        if ($isCacheable) {
            //maybe it's in cache
            $key = md5('ci' . get_called_class() . $name . $this->data[$this->primary_key]);
            $fromcache = Cache::get($key);
            if ($fromcache) {
                return $fromcache;
            }
        }


        if (!$this->fetched && !$this->data[$name] && $this->data[$this->primary_key]) {
            $this->loaddata();
        }



        if (is_int($this->data[$name]))
            $this->data[$name] = intval($this->data[$name]);
        if (is_float($this->data[$name]))
            $this->data[$name] = floatval($this->data[$name]);


        if ($this->isValidDateTime($this->data[$name])) {
            return new \TinyORM\DateTime($this->data[$name], new \DateTimeZone('Europe/Istanbul'));
        }


        if ($isCacheable) {
            Cache::set($key, $this->data[$name], 60 * 60);
        }
        return $this->data[$name];
    }

    public static function find($criteria) {

        $class_name = get_called_class();

        $object = new $class_name;

        $query = "SELECT * FROM `" . $object->table . "` WHERE 1  ";

        if (is_array($criteria)) {
            $params = array();
            foreach ($criteria as $field => $value) {
                $query.=" AND `" . $field . "`=?";
                $params[] = $value;
            }
        } else {
            $query.=" AND `" . $object->primary_key . "`=?";
            $params = array($criteria);
        }

        $query.=" limit 1";

        $fetched = Database::query($query)->execute($params)->fetchOne();
        $object->data = $fetched;
        $object->fetched = true;

        if ($fetched) {
            return $object;
        } else {
            return null;
        }
    }

    private function loaddata() {
        if (!$this->data[$this->primary_key] || $this->dontfetch) {
            return false;
        }



        $query = "SELECT * FROM `" . $this->table . "` WHERE 1 AND ";
        $query.=" `" . $this->primary_key . "`=?";
        $params = array($this->data[$this->primary_key]);
        $query.=" limit 1";

        $fetched = Database::query($query)->execute($params)->fetchOne();
        if ($fetched) {
            $this->data = $fetched;
            $this->fetched = true;
            return true;
        } else {
            return false;
        }
    }

    public function save() {

        if ($this->fetched) { //We know that this record exists in the table
            if (!$this->data[$this->primary_key]) {
                throw new Exception(get_called_class() . " instance does not have a value for its primary key field " . $this->primary_key);
            }
            return $this->update();
        }

        if (!$this->loaddata()) {
            return $this->insert();
        } else {
            return $this->update();
        }
    }

    private function insert() {
        $params = array();
        $fields = array();
        foreach ($this->data as $field => $value) {
            if ($value instanceof \TinyORM\DateTime) {
                if ($value->getInterval()) {
                    if ($value->getInterval() == 'now()') {
                        $v = array('column' => "`" . $field . "`", 'func' => "now()");
                    } else {
                        $v = array('column' => "`" . $field . "`", 'func' => "date_add(now(), interval " . $value->getInterval() . ")");
                    }
                } else {
                    $v = array('column' => "`" . $field . "`", 'value' => $value->format('Y-m-d H:i:s'));
                }
            } else {
                $v = array('column' => "`" . $field . "`", 'value' => $value);
            }

            $params[] = $v;
        }

        $query = "INSERT INTO `" . $this->table . "` ( ";
        for ($i = 0; $i < count($params); $i++) {
            $query.=$params[$i]['column'];
            if ($i < count($params) - 1) {
                $query.=",";
            }
        }
        $query.=") VALUES (";

        $realparams = array();

        for ($i = 0; $i < count($params); $i++) {
            if ($params[$i]['func']) {
                $query.=$params[$i]['func'];
            } else {
                $realparams[] = $params[$i]['value'];
                $query.="?";
            }
            if ($i < count($params) - 1) {
                $query.=",";
            }
        }

        $query.=")";

        try {
            Database::query($query)->execute($realparams);
            $insert_id = Database::get_insert_id();
            if ($insert_id) {
                return $insert_id;
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function update() {

        $params = array();
        $fields = array();
        foreach ($this->changed_items as $field) {

            $value = $this->data[$field];
            if ($value instanceof \TinyORM\DateTime) {
                if ($value->getInterval()) {
                    if ($value->getInterval() == 'now()') {
                        $fields[] = $field . "=now()";
                    } else {
                        $fields[] = $field . "=date_add(now(), interval " . $value->getInterval() . ")";
                    }
                } else {
                    $fields[] = $field . "=?";
                    $params[] = $value->format('Y-m-d H:i:s');
                }
            } else {
                $fields[] = $field . "=?";
                $params[] = $this->data[$field];
            }
        }

        $params[] = $this->data[$this->primary_key];
        $query = "UPDATE `" . $this->table . "` SET ";
        $query.=implode(',', $fields);
        $query.=" WHERE " . $this->primary_key . " = ?";
        $query.=" LIMIT 1";

        try {
            Database::query($query)->execute($params);
        } catch (Exception $e) {

            return false;
        }

        $this->changed_items = array();
        return true;
    }

    public function delete($criteria = null) {

        if (!$this->data[$this->primary_key] && !is_array($criteria)) {
            throw new Exception(get_called_class() . " instance does not have a value for its primary key field " . $this->primary_key);
        }

        $query = "DELETE FROM `" . $this->table . "` WHERE 1 AND ";
        if (is_array($criteria)) {
            $params = array();
            foreach ($criteria as $field => $value) {
                $query.=" `" . $field . "`=?";
                $params[] = $value;
            }
        } else {
            $query.=" `" . $this->primary_key . "`=?";
            $params = array($this->data[$this->primary_key]);
            $query.=" limit 1";
        }



        try {
            Database::query($query)->execute($params);
            return true;
        } catch (Exception $e) {

            return false;
        }
    }

}

?>