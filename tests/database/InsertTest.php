<?php

namespace VortechAPI\Tests\Database;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class InsertTest extends TestCase
{
    public function setUp()
    {
        $this->qb = new \Apps\Database\Insert();
    }

    public function testBasicQuery()
    {
        $queryBuilder = $this->qb;
        $sql = $queryBuilder->insert()->into('News (Title, Contents)')->values('"Hello", "Testing"')->result();
        $expected = 'INSERT INTO News (Title, Contents) VALUES ("Hello", "Testing")';

        $this->assertEquals($expected, $sql);
    }

    public function testAlternateQuery()
    {
        $queryBuilder = $this->qb;
        $sql = $queryBuilder->insert()->into('News')->values('"One", 123, null, NOW()')->result();
        $expected = 'INSERT INTO News VALUES ("One", 123, null, NOW())';

        $this->assertEquals($expected, $sql);
    }

    public function testQueryWithMissingInto()
    {
        $queryBuilder = $this->qb;
        $sql = $queryBuilder->insert()->into();
        $expected = 'Insert query missing target!';

        $this->assertEquals($sql, $expected);
    }

    public function testQueryWithMissingValues()
    {
        $queryBuilder = $this->qb;
        $sql = $queryBuilder->insert()->into('News')->values();
        $expected = 'Insert query missing values!';

        $this->assertEquals($sql, $expected);
    }
}
