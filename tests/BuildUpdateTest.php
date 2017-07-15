<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

class BuildUpdateTest extends TestCase
{
    public function __construct()
    {
        require_once('apps/database/update.php');
        $this->qb = new \VortechAPI\Apps\Database\BuildUpdate();
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

    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        throw new \InvalidArgumentException(
            sprintf(
                'Missing argument. %s %s %s %s',
                $errno,
                $errstr,
                $errfile,
                $errline
            )
        );
    }

    public function testBasicQueryWithMissingTargetTable()
    {
        set_error_handler(array($this, 'errorHandler'));
        $this->setExpectedException('\InvalidArgumentException');

        $queryBuilder = $this->qb;
        $queryBuilder->update()->set('Author = :name')->result();
    }

    public function testBasicQueryWithMissingValues()
    {
        $queryBuilder = $this->qb;
        $sql = $queryBuilder->update('News')->set()->where('NewsID = :id')->result();

        $this->assertEquals($sql, 'Update query missing values');
    }
}
