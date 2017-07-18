<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class DeleteTest extends TestCase
{
    public function __construct()
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

    public function testQueryWithMissingFrom()
    {
        // This should give a Missing arguments error due to the parameter being required
        set_error_handler(array($this, 'errorHandler'));
        $this->setExpectedException('\InvalidArgumentException');

        $queryBuilder = $this->qb;
        $queryBuilder->delete()->from()->where('NewsID = :id')->result();
    }

    public function testQueryWithMissingWhere()
    {
        // This should give a Missing arguments error due to the parameter being required
        set_error_handler(array($this, 'errorHandler'));
        $this->setExpectedException('\InvalidArgumentException');

        $queryBuilder = $this->qb;
        $queryBuilder->delete()->from('News')->where()->result();
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
}
