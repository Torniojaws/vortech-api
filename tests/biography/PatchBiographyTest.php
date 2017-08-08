<?php

namespace VortechAPI\Tests\Biography;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class PatchBiographyTest extends TestCase
{
    public function setUp()
    {
        $this->bio = new \Apps\Biography\PatchBiography();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->select = new \Apps\Database\Select();

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
        $sql = $sqlBuilder->delete()->from('Biography')->where('Full LIKE :full')->result();
        $pdo = array('full' => 'Full%');
        $this->database->run($sql, $pdo);
        $this->database->close();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->bio instanceof \Apps\Biography\PatchBiography);
    }

    public function testPatchingWithValidJSON()
    {
        $sql = $this->select->select()->from('Biography')->order('Created DESC')
            ->limit(1)->result();
        $pdo = array();
        $originalNewest = $this->database->run($sql, $pdo)[0];

        $json = '{"short": "Short patched biography"}';
        $response = $this->bio->patch($json);

        $patched = $this->database->run($sql, $pdo)[0];

        $this->assertFalse(empty($response));
        $this->assertEquals(204, $response['code']);
        $this->assertEquals($originalNewest['BiographyID'], $response['id'], 'Patch modified wrong entry');
        $this->assertNotEquals($originalNewest['Short'], $patched['Short'], 'Entry was not modified');
    }

    public function testPatchingWithValiJSONArray()
    {
        $sql = $this->select->select()->from('Biography')->order('Created DESC')
            ->limit(1)->result();
        $pdo = array();
        $originalNewest = $this->database->run($sql, $pdo)[0];

        $json = '[{"full": "Full patched biography"}, {"short": "Short patched biography"}]';
        $response = $this->bio->patch($json);

        $patched = $this->database->run($sql, $pdo)[0];

        $this->assertFalse(empty($response));
        $this->assertEquals(204, $response['code']);
        $this->assertEquals($originalNewest['BiographyID'], $response['id'], 'Patch modified wrong entry');
        $this->assertNotEquals($originalNewest['Full'], $patched['Full'], 'Full entry was not modified');
        $this->assertNotEquals($originalNewest['Short'], $patched['Short'], 'Short entry was not modified');
    }

    public function testPatchingWithInvalidJSONIsBadRequest()
    {
        $json = '{invalid';
        $response = $this->bio->patch($json);

        $this->assertFalse(empty($response));
        $this->assertEquals(400, $response['code']);
    }
}
