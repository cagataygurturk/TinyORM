<?php

/**
 * 
 *
 * @author cagatay
 */
use TinyORM\Database;

class FetcherTest extends PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
        Database::query("CREATE TABLE IF NOT EXISTS `TinyORMTest` (
                `username` varchar(50) NOT NULL,
                `value` varchar(255) NOT NULL,
                PRIMARY KEY (`username`)
              ) ENGINE=InnoDB DEFAULT CHARSET=latin5;")->execute();
    }

    public static function tearDownAfterClass() {
        Database::query("DROP TABLE IF EXISTS `TinyORMTest`")->execute();
    }

    public function testFetch() {
        $query = Database::query("show variables")->execute();
        $i = false;
        while ($tables = $query->fetch()) {
            $this->assertNotEmpty($query);
            $i = true;
            if ($i)
                break;
        }
    }

    public function testFetchNumeric() {
        $query = Database::query("show variables")->execute()->fetchNumeric();
        foreach ($query as $q) {
            $this->assertNotNull($q[0]);
        }
    }

   

    public function testPrepared() {
        
        $username='testusername';
        Database::query("replace into TinyORMTest (username, value) values ('".$username."','testvalue') ")->execute();
        
        $query = Database::query("select * from TinyORMTest where username=?")->execute(array($username))->fetchOne();
        $this->assertEquals($username, $query['username']);
    }
    
    public function testCached() {
        
        $username='testusername';
        Database::query("replace into TinyORMTest (username, value) values ('".$username."','testvalue') ")->execute();
        
        $query = Database::query("select * from TinyORMTest where username=?")->useResultCache(60, array($username));
        $this->assertEquals($username, $query[0]['username']);
    }
    

}

?>
