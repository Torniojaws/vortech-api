<?php

namespace VortechAPI\Tests\Biography;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class EditBiographyTest extends TestCase
{
    public function setUp()
    {
        $this->bio = new \Apps\Biography\EditBiography();
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
        $sql = $sqlBuilder->delete()->from('Biography')->where('Full = :full')->result();
        $pdo = array('full' => 'Full biography');
        $this->database->run($sql, $pdo);
        $this->database->close();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->bio instanceof \Apps\Biography\EditBiography);
    }

    public function testEditingBiographyWithJSONObjectReplacesData()
    {
        $json = '{"short": "Short edited biography", "full": "Full edited biography"}';
        $response = $this->bio->edit($json);

        $bio = new \Apps\Biography\GetBiography();
        $latest = $bio->get()['contents'];

        $this->assertFalse(empty($response));
        $this->assertEquals(200, $response['code']);
        $this->assertEquals('Short edited biography', $latest['Short'], 'Short bio is incorrect');
        $this->assertEquals('Full edited biography', $latest['Full'], 'Full bio is wrong');
        $this->assertFalse(empty($latest['Updated']));
    }

    public function testEditingBiographyWithJSONArrayReplacesData()
    {
        $json = '[{"short": "Short edited biography", "full": "Full edited biography"}]';
        $response = $this->bio->edit($json);

        $bio = new \Apps\Biography\GetBiography();
        $latest = $bio->get()['contents'];

        $this->assertFalse(empty($response));
        $this->assertEquals(200, $response['code']);
        $this->assertEquals('Short edited biography', $latest['Short'], 'Short bio is incorrect');
        $this->assertEquals('Full edited biography', $latest['Full'], 'Full bio is wrong');
        $this->assertFalse(empty($latest['Updated']));
    }
}
