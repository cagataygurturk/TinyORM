<?php

use TinyORM\Database;
use TinyORM\Model;

class UserMockObject extends Model {

    protected $table = 'TinyORMTest';
    protected $primary_key = "username";

}

class ModelTest extends PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
        Database::query("CREATE TABLE IF NOT EXISTS `TinyORMTest` (
                `username` varchar(50) NOT NULL,
                `value` varchar(255) NOT NULL,
                PRIMARY KEY (`username`)
              ) ENGINE=InnoDB DEFAULT CHARSET=latin5;")->execute();
    }

    public static function tearDownAfterClass() {
        Database::query("DROP TABLE IF EXISTS `TinyORMTest`");
    }

    public function testInsert() {

        $username = 'wondrous';
        $value = "test";

        $object = new UserMockObject();
        $object->username = $username;
        $object->value = $value;
        $object->save();

        $object2 = UserMockObject::find($username);
        $this->assertEquals($object2->value, $object->value);
    }

    public function testUpdate() {

        $username = 'wondrous';
        $value = "test_updated";
        $object = UserMockObject::find($username);
        $object->value = $value;
        $object->save();
        $object2 = UserMockObject::find($username);
        $this->assertEquals($object->value, $object2->value);
    }

    public function testFind() {
        $username = 'wondrous';
        $object = UserMockObject::find($username);
        $this->assertEquals($username, $object->username);
    }

    public function testDelete() {

        $username = 'wondrous';

        $object = UserMockObject::find($username);

        $this->assertTrue($object->delete());

        $object2 = UserMockObject::find($username);
        $this->assertNull($object2);
    }

}
?>
