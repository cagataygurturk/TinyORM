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

    public function __set($name, $value) {
        $this->data[$name] = $value;
        $this->changed_items[] = $name;
    }

    public function __get($name) {
        return $this->data[$name];
    }

    public static function find($criteria) {

        $class_name = get_called_class();

        $object = new $class_name;

        $query = "SELECT * FROM `" . $object->table . "` WHERE 1 AND ";

        if (is_array($criteria)) {
            $params = array();
            foreach ($criteria as $field => $value) {
                $query.=" `" . $field . "`=?";
                $params[] = $value;
            }
        } else {
            $query.=" `" . $object->primary_key . "`=?";
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

    public function save() {

        if (!$this->data[$this->primary_key]) {
            throw new Exception(get_called_class() . " instance does not have a value for its primary key field " . $this->primary_key);
        }

        if ($this->fetched) { //We know that this record exists in the table
            return $this->update();
        }

        return $this->insert();
    }

    private function insert() {
        $params = array();
        $fields = array();
        foreach ($this->data as $field => $value) {
            $params[] = $value;
            $fields[] = "`" . $field . "`";
        }


        $query = "INSERT INTO `" . $this->table . "` ( ";
        $query.=implode(',', $fields);
        $query.=") VALUES (?";
        for ($i = 1; $i < count($fields); $i++) {
            $query.=",?";
        }

        $query.=")";

        try {
            Database::query($query)->execute($params);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function update() {

        $params = array();
        $fields = array();
        foreach ($this->changed_items as $field) {
            $params[] = $this->data[$field];
            $fields[] = $field . "=?";
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
