<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class AddReleaseTest extends TestCase
{
    public function setUp()
    {
        $this->release = new \Apps\Releases\AddRelease();

        $this->add = new \Apps\Database\Insert();
        $this->get = new \Apps\Database\Select();
        $this->remove = new \Apps\Database\Delete();

        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->json = '{"title": "UnitTestAdder", "date": "2017-07-19 12:00:00", "artist": "UnitTesties",
            "credits": "This is very welcome", "people": [{"id": 1, "name": "UnitTestExampler",
            "instruments": "Synths"},{"id": 2, "name": "UnitTestBoombastic", "instruments": "Drums"}],
            "songs": [{"title": "UnitTest My Song", "duration": 305}, {"title": "UnitTest Another Piece",
            "duration": 125}, {"title": "UnitTest Helppo", "duration": 201}], "categories": [1, 2],
            "formats": [1, 3]}';
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->release instanceof \Apps\Releases\AddRelease);
    }

    /**
     * Add the new release to Releases and verify that it was added successfully.
     */
    public function testAddingRelease()
    {
        $this->release->add($this->json);

        $sql = $this->get->select()->from('Releases')->where('Title = :title')->limit(1)->result();
        $pdo = array('title' => 'UnitTestAdder');
        $result = $this->database->run($sql, $pdo);

        $expected = 'This is very welcome';

        $this->assertEquals($expected, $result[0]['Credits']);
    }

    public function testAddingReleaseWithInvalidJSON()
    {
        $response = $this->release->add('{"notvalid');
        $expected = 400;

        $this->assertEquals($expected, $response['code']);
    }

    /**
     * When a new release is added, it also creates (if needed) some related sets of data into
     * other tables. This tests the People table, which keeps track who played what instruments
     * on various albums.
     */
    public function testAddingReleaseCreatesRelatedPeopleEntries()
    {
        $this->release->add($this->json);

        $sql = $this->get->select()->from('People')->where('Name = :name')->limit(1)->result();
        $pdo = array('name' => 'UnitTestExampler');

        $result = $this->database->run($sql, $pdo);

        $this->assertFalse(empty($result));
    }

    public function testAddingReleaseCreatesRelatedReleasePeopleEntries()
    {
        $this->release->add($this->json);

        $sql = $this->get->select()->from('ReleasePeople')
            ->joins('JOIN People ON People.PersonID = ReleasePeople.PersonID')
            ->where('Name = :name')->limit(1)->result();
        $pdo = array('name' => 'UnitTestExampler');

        $result = $this->database->run($sql, $pdo);

        $expected = 'UnitTestExampler';
        $this->assertEquals($expected, $result[0]['Name']);
    }

    /**
     * When an album is added, it naturally contains songs. They will be stored in a separate
     * table, and there is also a second table where the information of which releases each
     * song appears on. This allows for the same song to be referenced in multiple albums, eg.
     * on a live album and also on the studio album.
     */
    public function testAddingSongsCreatesSongEntry()
    {
        $this->release->add($this->json);

        $sql = $this->get->select()->from('Songs')->where('Title = :title')->limit(1)->result();
        $pdo = array('title' => 'UnitTest Another Piece');
        $result = $this->database->run($sql, $pdo);

        $duration = intval($result[0]['Duration']);
        $expected = 125;

        $this->assertEquals($expected, $duration);
    }

    public function testAddingSongsCreatesTheAlbumReferenceEntry()
    {
        $this->release->add($this->json);

        $sql = $this->get->select()->from('ReleaseSongs')
            ->joins('JOIN Songs ON Songs.SongID = ReleaseSongs.SongID')
            ->where('Title = :title')->limit(1)->result();
        $pdo = array('title' => 'UnitTest Another Piece');
        $result = $this->database->run($sql, $pdo);

        $expected = 'UnitTest Another Piece';

        $this->assertEquals($expected, $result[0]['Title']);
    }

    /**
     * When a release is added, it usually comes in several formats eg. CD and Digital.
     * This information should be added to the per-release table. The values themselves are
     * predefined in Formats table.
     */
    public function testAddingReleaseFormatsCreatesFormatsEntries()
    {
        $this->release->add($this->json);

        $sql = $this->get->select()->from('ReleaseFormats')
            ->joins('JOIN Formats ON Formats.FormatID = ReleaseFormats.FormatID')
            ->where('Title = :title')->limit(1)->result();
        $pdo = array('title' => 'EP');
        $result = $this->database->run($sql, $pdo);

        $title = $result[0]['Title'];
        $expected = 'EP';

        $this->assertEquals($expected, $title);
    }

    /**
     * A release also has information about which category it belongs to, eg. full length,
     * EP, live album, compilation, split, etc. This tests that adding them works.
     */
    public function testAddingReleaseCategoriesCreatesEntries()
    {
        $this->release->add($this->json);

        $sql = $this->get->select()->from('ReleaseCategories')
            ->joins('JOIN ReleaseTypes ON ReleaseTypes.ReleaseTypeID = ReleaseCategories.ReleaseTypeID')
            ->where('Type = :type')->limit(1)->result();
        $pdo = array('type' => 'Live album');
        $result = $this->database->run($sql, $pdo);

        $title = $result[0]['Type'];
        $expected = 'Live album';

        $this->assertEquals($expected, $title);
    }

    public function tearDown()
    {
        $sql = $this->remove->delete()->from('Releases')->where('Title LIKE :title')->result();
        $pdo = array('title' => 'UnitTest%');
        $this->database->run($sql, $pdo);

        $sql = $this->remove->delete()->from('People')->where('Name LIKE :name')->result();
        $pdo = array('name' => 'UnitTest%');
        $this->database->run($sql, $pdo);

        $sql = $this->remove->delete()->from('Songs')->where('Title LIKE :stitle')->result();
        $pdo = array('stitle' => 'UnitTest%');
        $this->database->run($sql, $pdo);
        $this->database->close();
    }

    public function testDoInsertReleasePeopleWithInvalidIDs()
    {
        $result = $this->release->doInsertReleasePeople(null, null, 'test');

        $this->assertFalse($result);
    }

    public function testInsertReleasePeopleWithInvalidID()
    {
        $data = json_decode($this->json, true);

        $result = $this->release->insertReleasePeople($data, null);

        $this->assertFalse($result);
    }

    public function testInsertReleaseSongsWithInvalidIDs()
    {
        $data = json_decode($this->json, true);

        $result = $this->release->insertReleaseSongs($data, null);

        $this->assertFalse($result);
    }

    public function testDoInsertReleaseSongsWithInvalidIDs()
    {
        $result = $this->release->doInsertReleaseSongs(null, null);

        $this->assertFalse($result);
    }

    public function testInsertReleaseFormatsWithInvalidID()
    {
        $data = json_decode($this->json, true);
        $result = $this->release->insertReleaseFormats($data, "ABC");

        $this->assertFalse($result);
    }

    public function testInsertReleaseCategoriesWithInvalidID()
    {
        $data = json_decode($this->json, true);
        $result = $this->release->insertReleaseCategories($data, '?!?!');

        $this->assertFalse($result);
    }
}
