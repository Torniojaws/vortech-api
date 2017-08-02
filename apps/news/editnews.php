<?php

namespace Apps\News;

class EditNews
{
    public function edit(int $newsID, string $json)
    {
        $validator = new \Apps\Utils\Json();
        $dataIsValid = $validator->isJson($json);

        if ($dataIsValid) {
            $data = json_decode($json, true);
            $this->editNews($newsID, $data);

            // Update categories. We first delete all existing ones for current NewsID
            $this->deleteCategories($newsID);
            foreach ($data['categories'] as $category) {
                $this->addUpdatedCategory($category, $newsID);
            }
        }

        $response['code'] = 200;
        $response['contents'] = array();
        return $response;
    }

    /**
     * This will modify the News table in the DB using the new values provided in $data
     * @param int $newsID is the ID of the news to edit
     * @param array $news Contains the new values to use
     * @return boolean Was update query successful or no (even if nothing changes!)
     */
    public function editNews(int $newsID, array $news)
    {
        $database = new \Apps\Database\Database();
        $database->connect();

        $sqlBuilder = new \Apps\Database\Update();
        $sql = $sqlBuilder->update('News')
            ->set('Title = :title, Contents = :contents, Updated = NOW()')
            ->where('NewsID = :id')->result();
        $pdo = array('title' => $news['title'], 'contents' => $news['contents'], 'id' => $newsID);
        $database->run($sql, $pdo);

        return $database->isQuerySuccessful();
    }

    /**
     * When the news is updated, we will replace all existing categories in the NewsCategories table
     * for the current NewsID. Basically we delete the existing values, and add each new one. This
     * method does the adding.
     * @param int $category Is the ID of the category we will add
     * @param int $newsID Is the news ID we refer to
     * @return boolean The ID of the entry we inserted.
     */
    public function addUpdatedCategory(int $category, int $newsID)
    {
        $database = new \Apps\Database\Database();
        $database->connect();

        $sqlBuilder = new \Apps\Database\Insert();
        $sql = $sqlBuilder->insert()->into('NewsCategories(NewsID, CategoryID)')
            ->values(':id, :cid')->result();
        $pdo = array('id' => $newsID, 'cid' => $category);
        $database->run($sql, $pdo);

        return $database->getInsertId();
    }

    /**
     * As part of the PUT process, we remove existing NewsCategories for the current NewsID.
     * @param int $newsID Is the news ID that we use to delete the existing categories.
     */
    public function deleteCategories(int $newsID)
    {
        $database = new \Apps\Database\Database();
        $database->connect();

        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('NewsCategories')->where('NewsID = :id')->result();
        $pdo = array('id' => $newsID);

        $database->run($sql, $pdo);
    }
}
