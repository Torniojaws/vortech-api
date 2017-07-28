<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class DeleteTest extends TestCase
{
    public function setUp()
    {
        $this->qb = new \Apps\Database\Delete();
    }

    public function testBasicQuery()
    {
        $queryBuilder = $this->qb;
        $sql = $queryBuilder->delete()->from('News')->where('NewsID = :id')->result();
        $expected = 'DELETE FROM News WHERE NewsID = :id';

        $this->assertEquals($expected, $sql);
    }

    public function testBasicQueryWithMultipleCriteria()
    {
        $queryBuilder = $this->qb;
        $sql = $queryBuilder->delete()->from('News')->where('NewsID = :id AND Author = :author')->result();
        $expected = 'DELETE FROM News WHERE NewsID = :id AND Author = :author';

        $this->assertEquals($expected, $sql);
    }

    public function testQueryWithMissingWhere()
    {
        $queryBuilder = $this->qb;
        $sql = $queryBuilder->delete()->from('News')->where();
        $expected = 'You must use WHERE in all DELETE queries to this API';

        $this->assertEquals($sql, $expected);
    }

    public function testComplexQuery()
    {
        // This is just a light test on the probably unused multi-table deletion
        $queryBuilder = $this->qb;
        $sql = $queryBuilder->delete('News, Comments')->from('News')->joins('JOIN Comments ON Comments.NewsID = :id')
            ->where('News.NewsID = :nid')->result();
        $expected = 'DELETE News, Comments FROM News JOIN Comments ON Comments.NewsID = :id WHERE News.NewsID = :nid';

        $this->assertEquals($expected, $sql);
    }

    public function testQueryWithMissingJoins()
    {
        $queryBuilder = $this->qb;
        $sql = $queryBuilder->delete('News, Comments')->from('News')->joins();
        $expected = 'When deleting from multiple tables, joins are required';

        $this->assertEquals($sql, $expected);
    }
}
