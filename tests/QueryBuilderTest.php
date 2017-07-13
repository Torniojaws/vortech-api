<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    public function __construct()
    {
        require_once('apps/database/query.php');
        $this->qb = new \VortechAPI\Apps\Database\QueryBuilder();
    }

    public function testBasicQuery()
    {
        $queryBuilder = $this->qb;
        $sql = $queryBuilder->select()->from('News')->limit(1)->result();
        $expected = 'SELECT * FROM News LIMIT 1';

        $this->assertEquals($expected, $sql);
    }

    public function testSpecificSelects()
    {
        $queryBuilder = $this->qb;
        $sql = $queryBuilder->select('Title, Contents')->from('News')->where('NewsID = :id')->result();
        $expected = 'SELECT Title, Contents FROM News WHERE NewsID = :id';

        $this->assertEquals($expected, $sql);
    }

    public function testSelectOneColumnMightFail()
    {
        $queryBuilder = $this->qb;
        $sql = $queryBuilder->select('DATE(Created)')->from('News')->limit(1)->result();
        $expected = 'SELECT DATE(Created) FROM News LIMIT 1';

        $this->assertEquals($expected, $sql);
    }

    public function testGrouping()
    {
        $queryBuilder = $this->qb;
        $sql = $queryBuilder->select()->from('News')->group('DATE(Created)')->result();
        $expected = 'SELECT * FROM News GROUP BY DATE(Created)';

        $this->assertEquals($expected, $sql);
    }
}
