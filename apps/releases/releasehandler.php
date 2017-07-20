<?php

namespace Apps\Releases;

class ReleaseHandler
{
    public function __construct()
    {
        // We always run a query when this class is instantiated, so let's connect automatically
        $this->db = new \Apps\Database\Database();
        $this->db->connect();

        // These allow us to build queries reliably
        $this->buildSelect = new \Apps\Database\Select();
        $this->buildInsert = new \Apps\Database\Insert();
        $this->buildUpdate = new \Apps\Database\Update();
        $this->buildDelete = new \Apps\Database\Delete();
        $this->arrayUtils = new \Apps\Utils\Arrays();
    }

    public function getReleases($params)
    {
        $response = array();
        $pdoParams = array();

        $sql = $this->buildSelect->select()->from('Releases')->result();

        if (is_numeric($params[1])) {
            // GET /releases/:id
            $sql = $this->buildSelect->select()->from('Releases')->where('ReleaseID = :id')->result();

            // GET /releases/:id/comments
            if (isset($params[2]) && $params[2] == 'comments') {
                $sql = $this->buildSelect->select()->from('ReleaseComments')->where('ReleaseID = :id')->result();
            }

            $pdoParams = array('id' => $params[1]);
        }

        // Now we can run the query, which uses a PDO prepared statement
        $results = $this->db->run($sql, $pdoParams);

        $response['contents'] = $results;
        $response['code'] = 200;
        return $response;
    }

    /**
     * Add a Release to the database, and insert the related things to their own tables
     * @param string $data Contains the raw input from php://input, which should be a JSON
     * @return array $response The array of the results
     */
    public function addRelease($data)
    {
        $response = array();

        // Then get the data from an associative array of the JSON
        $json = json_decode($data, true);
        $title = $json['title'];
        $date = $json['date'];
        $artist = $json['artist'];
        $credits = $json['credits'];

        // Write the release to the DB
        $sql = $this->buildInsert->insert()->into('Releases(Title, Date, Artist, Credits, Created)')
            ->values(':title, :date, :artist, :credits, NOW()')->result();
        $pdoParams = array('title' => $title, 'date' => $date, 'artist' => $artist, 'credits' => $credits);

        // Now we can run the query, which uses a PDO prepared statement
        $this->db->run($sql, $pdoParams);
        $currentReleaseID = $this->db->getInsertId();

        // These will require iteration upon insert, and they go to their own tables
        $this->insertPeople($json['people'], $currentReleaseID);
        $this->insertSongs($json['songs'], $currentReleaseID);
        $this->insertCategories($json['categories'], $currentReleaseID);
        $this->insertFormats($json['formats'], $currentReleaseID);

        $response['contents'] = "Location: http://www.vortechmusic.com/api/1.0/releases/".$currentReleaseID;
        $response['id'] = $currentReleaseID;
        $response['code'] = 201; // https://tools.ietf.org/html/rfc7231#section-4.3
        return $response;
    }

    /**
     * This will update the ReleaseCategories table with the new release details
     * @param array $data Has the category IDs as a flat array
     * @return boolean Everything added successfully?
     */
    public function insertCategories($data, $releaseID)
    {
        $errors = 0;
        foreach ($data as $category) {
            $sql = $this->buildInsert->insert()->into('ReleaseCategories(ReleaseID, CategoryID)')
                ->values(':id, :category')->result();
            $pdoParams = array('id' => $releaseID, 'category' => $category);
            $this->db->run($sql, $pdoParams);

            // Validate (we should get the unique ID of the insert back upon success)
            $lastID = $this->db->getInsertId();
            if (is_numeric($lastID) == false) {
                $errors++;
            }
        }

        return $errors == 0;
    }

    /**
     * This will update the ReleasePeople table with the new release people.
     * $data[0]['id'] = ID of person (separate People table)
     * $data[0]['instruments'] = instruments he played (string)
     * etc
     * @param array $data Has an array of arrays for different info per person
     * @return boolean Everything added successfully?
     */
    public function insertPeople($people, $releaseID)
    {
        $errors = 0;
        foreach ($people as $person) {
            if (isset($person['new']) && $person['new'] == true) {
                // TODO: If person does not exist yet, add new entry to People table before continuing
                // For now, just return, since we cannot insert into ReleasePeople without the PersonID
                // The name for new people is in: $person['name']
                return;
            }

            // Add the details
            $sql = $this->buildInsert->insert()->into('ReleasePeople(ReleaseID, PersonID, Instruments)')
                ->values(':id, :person, :instruments')->result();
            $pdoParams = array('id' => $releaseID, 'person' => $person['id'], 'instruments' => $person['instruments']);
            $this->db->run($sql, $pdoParams);

            // Validate (we should get the unique ID of the insert back upon success)
            $lastID = $this->db->getInsertId();
            if (is_numeric($lastID) == false) {
                $errors++;
            }
        }

        return $errors == 0;
    }

    /**
     * This will update the ReleaseSongs table with the new release song info
     * @param array $data Has the category IDs as a flat array
     * @return boolean Everything added successfully?
     */
    public function insertSongs($songs, $releaseID)
    {
        $errors = 0;
        foreach ($songs as $song) {
            // TODO: How to handle possible compilations or live releases, when the songs already exist in DB?

            // Get the songID from the database. If -1 is returned, it does not exist and will be added
            $songID = $this->getSongID($song['title']);

            // When the songs are new, we need to add them to the Songs table
            if ($songID == -1) {
                $sql = $this->buildInsert->insert()-into('Songs(Title, Duration)')->
                    values(':title, :duration')->result();
                $pdoParams = array('title' => $song['title'], 'duration' => $song['duration']);
                $this->db->run($sql, $pdoParams);
                // Validate
                $songID = $this->db->getInsertId();
                if (is_numeric($songID) == false) {
                    $errors++;
                }
            }

            // When song is sure to exist, we add the per-release reference
            $sql = $this->buildInsert->insert()->into('ReleaseSongs(ReleaseID, SongID)')
                ->values(':id, :song')->result();
            $pdoParams = array('id' => $releaseID, 'song' => $songID);
            $this->db->run($sql, $pdoParams);

            // Validate (we should get the unique ID of the insert back upon success)
            $lastID = $this->db->getInsertId();
            if (is_numeric($lastID) == false) {
                $errors++;
            }
        }

        return $errors == 0;
    }

    /**
     * Check by song title whether a given song exists in the database
     * @param string $title Has the song title to search for
     * @return int The ID or -1 when nothing was found
     */
    public function getSongID($title)
    {
        $sql = $this->buildSelect->select('SongID')->from('Songs')->where('Title = :title')
            ->limit(1)->result();
        $pdoParam = array('title' => $title);
        $result = $this->db->run($sql, $pdoParam);
        if ($result == false) {
            $result = -1;
        }

        return $result;
    }

    /**
     * This will update the ReleaseFormats table with the new release formats.
     * A format is for example CD, Digital, Vinyl, etc.
     * @param array $formats Has an array of the format IDs. If it does not exist in DB, add it.
     * @return boolean Everything added successfully?
     */
    public function insertFormats($formats, $releaseID)
    {
        $errors = 0;
        foreach ($formats as $format) {
            // If the format does not exist yet, we will add it
            $formatExists = $this->doesFormatExist($format['title']);

            if ($formatExists == false) {
                $sql = $this->buildInsert->insert()->into('ReleaseFormats(Title)')->values(':title')->result();
                $pdoParams = array('title' => $format['title']);
                $this->db->run($sql, $pdoParams);
            }

            // Add the details
            $sql = $this->buildInsert->insert()->into('ReleasePeople(ReleaseID, PersonID, Instruments)')
                ->values(':id, :person, :instruments')->result();
            $pdoParams = array('id' => $releaseID, 'person' => $person['id'], 'instruments' => $person['instruments']);
            $this->db->run($sql, $pdoParams);

            // Validate (we should get the unique ID of the insert back upon success)
            $lastID = $this->db->getInsertId();
            if (is_numeric($lastID) == false) {
                $errors++;
            }
        }

        return $errors == 0;
    }

    public function doesFormatExist($title)
    {
        $sql = $this->buildSelect->select('COUNT(*)')->from('ReleaseFormats')->where('Title = :title')
            ->limit(1)->result();
        $pdoParams = array('title' => $title);
        $matches = $this->db->run($sql, $pdoParams);

        return is_numeric($matches) && $matches == 1;
    }

    public function editRelease($params, $json)
    {
        $response = array();

        // We only proceed if we have a ReleaseID
        if (is_numeric($params[1]) == false) {
            $response['contents'] = 'Missing ReleaseID from URL';
            $response['code'] = 400;
            return $response;
        }

        // Convert the JSON into an array and get the data
        $json = json_decode($data, true);
        $title = $json['title'];
        $contents = $json['contents'];
        $categories = $json['categories'];

        // Update the News entry
        $sql = $this->buildUpdate->update('News')
            ->set('Title = :title, Contents = :contents, Updated = NOW()')
            ->where('NewsID = :id')->result();
        $pdoParams = array('id' => $params[1], 'title' => $title, 'contents' => $contents);

        // Now we can run the query, which uses a PDO prepared statement
        $this->db->run($sql, $pdoParams);

        // And categories. This is a bit tricky, since each entry has its own row in the table
        // So we check what exists already
        $sql = $this->buildSelect->select('DISTINCT(CategoryID)')->from('NewsCategories')
            ->where('NewsID = :id')->result();
        $pdoParams = array('id' => $params[1]);
        $existingCategoryIds = $this->db->run($sql, $pdoParams);

        // The data is in an array of arrays, so let's convert it to a plain array(1, 2, 3)
        $flat = $this->arrayUtils->flattenArray($existingCategoryIds, 'CategoryID');
        $flatExisting = $this->arrayUtils->toIntArray($flat);

        // Then we iterate the new values (array of integers)
        foreach ($categories as $category) {
            // If the new category is not in the existingCategoryIds, we INSERT it
            if (in_array($category, $flatExisting) == false) {
                $sql = $this->buildInsert->insert()->into('NewsCategories(NewsID, CategoryID)')
                    ->values(':id, :category')->result();
                $pdoParams = array('id' => $params[1], 'category' => $category);
                $this->db->run($sql, $pdoParams);
                // To prevent duplicates, we add the new entry to the array
                $flatExisting[] = $category;
            }

            // If the existingCategoryId does not exist in the new categories, we DELETE it
            foreach ($flatExisting as $old) {
                if (in_array($old, $categories) == false) {
                    $sql = 'DELETE FROM NewsCategories
                            WHERE CategoryID = :id';
                    $pdoParams = array('id' => $old);
                    $this->db->run($sql, $pdoParams);
                }
            }
        }
        // All done
        $response['contents'] = "Location: http://www.vortechmusic.com/api/1.0/news/".$params[1];
        $response['code'] = 200;
        return $response;
    }
}
