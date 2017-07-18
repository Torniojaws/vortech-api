<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class UpdateTest extends TestCase
{
    public function __construct()
    {
        $this->qb = new \Apps\Database\Update();
    }

    public function testBasicQueryWithoutWhere()
    {
        $queryBuilder = $this->qb;
        $sql = $queryBuilder->update('News')->set('Updated = NOW()')->result();
        $expected = 'UPDATE News SET Updated = NOW()';

        $this->assertEquals($expected, $sql);
    }

    public function testBasicQueryWithWhere()
    {
        $queryBuilder = $this->qb;
        $sql = $queryBuilder->update('News')->set('Author = :name')->where('NewsID = :id')->result();
        $expected = 'UPDATE News SET Author = :name WHERE NewsID = :id';

        $this->assertEquals($expected, $sql);
    }

    public function testComplexQuery()
    {
        $queryBuilder = $this->qb;
        $sql = $queryBuilder->update('News, NewsCategories')->set('News.Author = :name, NewsCategories.CategoryID')
            ->where('News.NewsID = NewsCategories.NewsID AND News.Title = :title')->result();
        $expected = 'UPDATE News, NewsCategories SET News.Author = :name, NewsCategories.CategoryID ';
        $expected .= 'WHERE News.NewsID = NewsCategories.NewsID AND News.Title = :title';

        $this->assertEquals($expected, $sql);
    }

    public function testBasicQueryWithMissingTargetTable()
    {
        $queryBuilder = $this->qb;
        $sql = $queryBuilder->update();
        $expected = 'Update query missing target table';

        $this->assertEquals($sql, $expected);
    }

    public function testBasicQueryWithMissingValues()
    {
        $queryBuilder = $this->qb;
        $sql = $queryBuilder->update('News')->set();
        $expected = 'Update query missing values';

        $this->assertEquals($sql, $expected);
    }
}
