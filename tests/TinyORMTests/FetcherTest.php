<?php

/**
 * 
 *
 * @author cagatay
 */
use TinyORM\Database;

class FetcherTest extends PHPUnit_Framework_TestCase {

    protected function setUp() {
        
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

    public function testFetchOne() {
        $query = Database::query("show variables")->execute()->fetchOne();
        
        $this->assertEquals(1, count($query));
    }

}

?>
