<?php

namespace VortechAPI\Tests\Biography;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class GetBiographyTest extends TestCase
{
    public function setUp()
    {
        $this->bio = new \Apps\Biography\GetBiography();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        // Add two biographies, to test that we get the latest
        $short = 'Short old bio';
        $full = 'Full old biography';
        $insert = new \Apps\Database\Insert();
        $sql = $insert->insert()->into('Biography(Short, Full, Created)')
            ->values(':short, :full, NOW() - INTERVAL 1 DAY')->result();
        $pdo = array('short' => $short, 'full' => $full);
        $this->database->run($sql, $pdo);

        $short = 'Short bio';
        $full = 'Full biography';
        $insert = new \Apps\Database\Insert();
        $sql = $insert->insert()->into('Biography(Short, Full, Created)')
            ->values(':short, :full, NOW()')->result();
        $pdo = array('short' => $short, 'full' => $full);
        $this->database->run($sql, $pdo);
    }

    public function tearDown()
    {
        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('Biography')->where('Full = :full')->result();
        $pdo = array('full' => 'Full biography');
        $this->database->run($sql, $pdo);
        $this->database->close();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->bio instanceof \Apps\Biography\GetBiography);
    }

    public function testGettingBioReturnsLatestBio()
    {
        $response = $this->bio->get();

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertEquals('Full biography', $response['contents']['Full'], 'Wrong bio');
    }
}
