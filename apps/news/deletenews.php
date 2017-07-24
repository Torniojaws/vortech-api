<?php

namespace Apps\News;

class DeleteNews
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();
    }

    public function delete($newsID)
    {
        if ($this->newsIDExists($newsID) == false) {
            $response['contents'] = 'News not found';
            $response['code'] = 400;
            return $response;
        }

        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('News')->where('NewsID = :id')->result();
        $pdo = array('id' => $newsID);
        $this->database->run($sql, $pdo);
    }

    /**
     * TODO: Yes, this is a candidate for moving to a Util class along with releaseIDExists. Both
     * work the same way, so it should be a generic method for checking.
     */
    public function newsIDExists($newsID)
    {
        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('COUNT(*) AS Count')->from('News')->where('NewsID = :id')
            ->limit(1)->result();
        $pdo = array('id' => $newsID);
        $result = $this->database->run($sql, $pdo);
        $count = intval($result[0]['Count']);

        return $count > 0;
    }
}
