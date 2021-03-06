<?php

use TinyORM\Database;
use TinyORM\Model;

class UserMockObject extends Model
{

    protected $table = 'TinyORMTest';
    protected $primary_key = "username";

}

class UserMockObjectCacheable extends Model
{

    protected $table = 'TinyORMTest';
    protected $primary_key = "username";
    protected $cacheable = true;
    protected $cachable_fields = array('id');

    public function getUsername()
    {
        return $this->username;
    }

    public function getId()
    {
        return $this->id;
    }


}

class ModelTest extends PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {

        Database::query(
            "CREATE TABLE IF NOT EXISTS `TinyORMTest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `value` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `datetime` datetime NOT NULL,
   PRIMARY KEY (`id`),
   UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin5;"
        )->execute();
    }

    public static function tearDownAfterClass()
    {
        Database::query("DROP TABLE IF EXISTS `TinyORMTest`")->execute();
    }

    public function testInsert()
    {

        $username = 'wondrous';
        $value    = "test";

        $object           = new UserMockObject();
        $object->username = $username;
        $object->value    = $value;
        $object->save();

        $object2 = UserMockObject::find($username);
        $this->assertEquals($object2->value, $object->value);
    }

    public function testInsertWithoutPrimaryKey()
    {

        $value = "test";

        $object        = new UserMockObject();
        $object->value = $value;
        $object->save();

        $object2 = UserMockObject::find(array('value' => $value));
        $this->assertEquals($object2->value, $object->value);
    }

    public function testUpdate()
    {

        $username      = 'wondrous';
        $value         = "test_updated";
        $object        = UserMockObject::find($username);
        $object->value = $value;
        $object->save();
        $object2 = UserMockObject::find($username);
        $this->assertEquals($object->value, $object2->value);
    }

    public function testFind()
    {
        $username = 'wondrous';
        $object   = UserMockObject::find($username);
        $this->assertNotNull($object);
    }

    public function testFindByCriterias()
    {
        $username = 'wondrous';
        $object   = UserMockObject::find(array('username' => $username));
        $this->assertNotNull($object);
    }

    public function testDelete()
    {

        $username = 'wondrous';

        $object = UserMockObject::find($username);

        $this->assertNotNull($object);
        $this->assertTrue($object->delete());

        $object2 = UserMockObject::find($username);
        $this->assertNull($object2);
    }

    public function testSetDateTime()
    {

        $username         = 'wondrous';
        $value            = "test";
        $object           = new UserMockObject();
        $object->username = $username;
        $object->value    = $value;
        $object->date     = new \TinyORM\DateTime();
        $object->date->setNow();
        $object->time = new \TinyORM\DateTime();
        $object->time->setNow();
        $object->datetime = new \TinyORM\DateTime();
        $object->datetime->setYesterday();
        $object->save();

        $object2 = UserMockObject::find($username);


        $this->assertInstanceOf('\TinyORM\DateTime', $object2->date);
        $this->assertInstanceOf('\TinyORM\DateTime', $object2->datetime);


        $object2->username = 'wondrous';
        $object2->datetime = new \TinyORM\DateTime('2011-02-23', new \DateTimeZone('Europe/Istanbul'));

        $object2->save();

        $object3 = UserMockObject::find($username);
        $this->assertInstanceOf('\TinyORM\DateTime', $object3->datetime);

        $this->assertNotEquals($object->datetime->format('Y-m-d H:i:s'), $object3->datetime->format('Y-m-d H:i:s'));

        $object4       = new UserMockObject();
        $object4->date = '2013-02-03';
        $this->assertInstanceOf('\TinyORM\DateTime', $object4->date);
    }

    public function testFindCache()
    {
        $username         = 'cachetest';
        $value            = 'cachetestvalue';
        $object           = new UserMockObjectCacheable();
        $object->username = $username;
        $object->value    = $value;
        $id               = $object->save();

        $object2 = UserMockObjectCacheable::find($username);
        $object3 = UserMockObjectCacheable::find($username);
        $this->assertTrue($object3->isCacheable());
        $this->assertEquals($value, $object2->value);
        $this->assertEquals($value, $object3->value);
        //$this->assertEquals($id, $object2->getId());
        $this->assertEquals($id, $object3->getId());
        $this->assertTrue($object3->fetchedFromCache());

    }


}

?>
