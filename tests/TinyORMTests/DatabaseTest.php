<?php

use TinyORM\Database;

class DatabaseTest extends PHPUnit_Framework_TestCase {


    protected function setUp() {
    }

    public function testDatabaseConnectivity() {

        $db=Database::query("select 1 from entries limit 1")->execute()->fetchScalar();
        $this->assertEquals(1, $db);
    }

}

?>
