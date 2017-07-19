<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class DatabaseTest extends TestCase
{
    public function __construct()
    {
        $this->db = new \Apps\Database\Database();
    }

    public function testPDOConnectionThatShouldWork()
    {
        $this->db->connect();

        $this->assertTrue($this->db->pdo instanceof \PDO);
    }

    public function testClosingPDOConnection()
    {
        $this->db->connect();
        $pdo = $this->db->close();

        $this->assertTrue($pdo == null);
    }

    public function testRunningQueryThatCausesAnException()
    {
        $this->db->connect();
        $this->setExpectedException(\PDOException::class);
        $run = $this->db->run('ASDASD', array(null, -1));
    }
}
