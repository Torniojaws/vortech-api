<?php

namespace Apps\Shows;

class PatchShow extends \Apps\Abstraction\CRUD
{
    public function patch(int $showID, string $json)
    {
        if ($this->json->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        // Rely that user passes correct values, or nothing is updated
        $show = json_decode($json, true);
        if (is_array($show) == false) {
            $show = array($show);
        }

        try {
            foreach ($show as $column => $value) {
                // Update a different table based on the column key
                switch ($column) {
                    case 'setlist':
                        $this->patchSetlist($showID, $value);
                        break;
                    case 'otherBands':
                        $this->patchBands($showID, $value);
                        break;
                    case 'performers':
                        $this->patchPerformers($showID, $value);
                        break;
                    default:
                        $this->patchShow($showID, $column, $value);
                        break;
                }
            }
            $response['code'] = 204;
            $response['contents'] = array();
        } catch (\PDOException $ex) {
            // Usually if column does not exist
            $response['code'] = 400;
            $response['contents'] = 'Could not patch data';
        }

        return $response;
    }

    /**
     * Replace the show setlist. If setlist contains a non-integer item, it is a new song and will
     * be added to Songs, unless it already exists there.
     * @param int $showID is the show we update
     * @param array $songs is the array of songs we will use
     */
    public function patchSetlist(int $showID, array $songs)
    {
        // Since the functionality is identical to the one in EditShows, we'll use that directly
        $edit = new \Apps\Shows\EditShow();
        $edit->updateSetlist($showID, $songs);
    }

    /**
     * Update the show's other bands if there is a new band added. We won't delete anything.
     * @param int $showID is the show we update
     * @param array $songs is the array of songs we will use
     */
    public function patchBands(int $showID, array $bands)
    {
        if (is_array($bands) == false) {
            $bands = array($bands);
        }
        foreach ($bands as $band) {
            $exists = $this->dbCheck->existsInTable('ShowsOtherBands', 'BandName', $band['name']);
            if ($exists == false) {
                $sql = $this->create->insert()->into('ShowsOtherBands(ShowID, BandName, BandWebsite)')
                    ->values(':show, :name, :web')->result();
                $pdo = array('show' => $showID, 'name' => $band['name'], 'web' => $band['website']);
                $this->database->run($sql, $pdo);
            }
        }
    }

    /**
     * Update the show performers. If performer is a non-integer item, it is a new person and will
     * be added to People, unless they already exist there. We will not delete any existing entries.
     * @param int $showID is the show we update
     * @param array $songs is the array of songs we will use
     */
    public function patchPerformers(int $showID, array $people)
    {
        foreach ($people as $person) {
            $personID = $person['personID'];
            if (is_numeric($personID) == false) {
                // Check does the person exist already, by name
                $exists = $this->dbCheck->existsInTable('People', 'Name', $personID);
                if ($exists == false) {
                    // Add new Person
                    $addPerson = new \Apps\People\AddPeople();
                    $addPerson->add(json_encode(array('name' => $person['personID'])));
                }
                // Get the PersonID
                $sql = $this->read->select('PersonID')->from('People')->where('Name = :name')->result();
                $pdo = array('name' => $personID);
                $result = $this->database->run($sql, $pdo);
                $personID = intval($result[0]['PersonID']);
            }

            // Check if the ShowsPeople already contains the given PersonID. If not, add it.
            $inShow = $this->dbCheck->existsInTable('ShowsPeople', 'PersonID', $personID);
            if ($inShow == false) {
                $sql = $this->create->insert()->into('ShowsPeople(ShowID, PersonID, Instruments)')
                    ->values(':show, :pid, :instruments')->result();
                $pdo = array('show' => $showID, 'pid' => $personID, 'instruments' => $person['instruments']);
                $this->database->run($sql, $pdo);
            }
        }
    }

    /**
     * Update a Show detail.
     * @param int $showID is the show we update
     * @param string $column is the DB column we update
     * @param string $value is the value we'll use
     */
    public function patchShow(int $showID, string $column, string $value)
    {
        // Special case
        if ($column == 'date') {
            $column = 'ShowDate';
        }

        $sql = $this->update->update('Shows')->set($column.' = :value')->where('ShowID = :id')->result();
        $pdo = array('value' => $value, 'id' => $showID);
        $this->database->run($sql, $pdo);
    }
}
