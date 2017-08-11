<?php

namespace Apps\News;

class PatchNews extends \Apps\Abstraction\CRUD
{
    /**
     * We must rely that the user provides correct values for the table columns.
     * Patching News categories will have its own endpoint
     * @param int $newsID
     * @param string $json contains the data we use for patching
     */
    public function patch(int $newsID, string $json)
    {
        try {
            $items = json_decode($json, true);

            if ($this->dbCheck->existsInTable('News', 'NewsID', $newsID) == false) {
                $response['contents'] = 'Unknown news ID';
                $response['code'] = 400;
                return $response;
            }

            // Categories exist in their own table.
            $success = $this->updateCategories($newsID, $items);
            if ($success == false) {
                $response['contents'] = 'Invalid category data';
                $response['code'] = 400;
                return $response;
            }

            // All other valid items exist in the News table
            foreach ($items as $column => $value) {
                $sql = $this->update->update('News')->set($column.' = :value')
                    ->where('NewsID = :id')->result();
                $pdo = array('value' => $value, 'id' => $newsID);
                $this->database->run($sql, $pdo);
            }

            $response['contents'] = array();
            $response['code'] = 204;
        } catch (\PDOException $exception) {
            // Most likely when column does not exist
            $response['contents'] = 'Patch failed. Columns OK? ID is OK?';
            $response['code'] = 400;
        }

        return $response;
    }

    /**
     * To update categories, we delete the old entries and add new ones in from the patch.
     * @param int $newsID Is the News to update
     * @param array $items Contains an array that might have the key 'categories'
     * @return boolean True when no errors appeared. If errors, returns false
     */
    public function updateCategories(int $newsID, array $items)
    {
        // Don't check if no categories are given
        if (isset($items['categories']) == false) {
            return true;
        }

        // Validate the categories. Must be integers
        if ($this->arrays->arrayContainsNonIntegers($items['categories'])) {
            return false;
        }

        // Delete previous categories
        $sql = $this->delete->delete()->from('NewsCategories')->where('NewsID = :id')->result();
        $pdo = array('id' => $newsID);
        $this->database->run($sql, $pdo);

        // Then add the new ones in
        foreach ($items['categories'] as $category) {
            $sql = $this->create->insert()->into('NewsCategories(NewsID, CategoryID)')
                ->values(':nid, :cid')->result();
            $pdo = array('nid' => $newsID, 'cid' => $category);
            $this->database->run($sql, $pdo);
        }

        return true;
    }
}
