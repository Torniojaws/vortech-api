<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

class BuildInsertTest extends TestCase
{
    public function __construct()
    {
        require_once('apps/database/insert.php');
        $this->qb = new \VortechAPI\Apps\Database\BuildInsert();
    }

    public function testBasicQuery()
    {
        $queryBuilder = $this->qb;
        $sql = $queryBuilder->insert()->into('News (Title, Contents)')->values('"Hello", "Testing"')->result();
        $expected = 'INSERT INTO News (Title, Contents) VALUES ("Hello", "Testing")';

        $this->assertEquals($expected, $sql);
    }
}
