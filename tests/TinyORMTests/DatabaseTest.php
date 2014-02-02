<?php

use TinyORM\Database;

class DatabaseTest extends PHPUnit_Framework_TestCase {

    public function testDatabaseConnectivity() {

        $db = Database::query("select 1")->execute()->fetchScalar();
        $this->assertEquals(1, $db);
    }

}

?>
