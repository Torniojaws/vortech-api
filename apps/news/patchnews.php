<?php

namespace Apps\News;

class PatchNews
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();
    }

    /**
     * We must rely that the user provides correct values for the table columns.
     * Patching News categories will have its own endpoint
     */
    public function patch($newsID, $json)
    {
        try {
            $items = json_decode($json, true);

            $check = new \Apps\Utils\DatabaseCheck();
            if ($check->existsInTable('News', 'NewsID', $newsID) == false) {
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
            $sqlBuilder = new \Apps\Database\Update();
            foreach ($items as $column => $value) {
                $sql = $sqlBuilder->update('News')->set($column.' = :value')
                    ->where('NewsID = :id')->result();
                $pdo = array('value' => $value, 'id' => $newsID);
                $this->database->run($sql, $pdo);
            }

            $response['contents'] = null;
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
    public function updateCategories($newsID, $items)
    {
        // Don't check if no categories are given
        if (isset($items['categories']) == false) {
            return true;
        }

        // Validate the categories. Must be integers
        $utils = new \Apps\Utils\Arrays();
        if ($utils->arrayContainsNonIntegers($items['categories'])) {
            return false;
        }

        // Delete previous categories
        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('NewsCategories')->where('NewsID = :id')->result();
        $pdo = array('id' => $newsID);
        $this->database->run($sql, $pdo);

        // Then add the new ones in
        foreach ($items['categories'] as $category) {
            $insert = new \Apps\Database\Insert();
            $sql = $insert->insert()->into('NewsCategories(NewsID, CategoryID)')
                ->values(':nid, :cid')->result();
            $pdo = array('nid' => $newsID, 'cid' => $category);
            $this->database->run($sql, $pdo);
        }

        return true;
    }
}
