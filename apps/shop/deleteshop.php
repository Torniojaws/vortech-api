<?php

namespace Apps\Shop;

class DeleteShop extends \Apps\Abstraction\CRUD
{
    public function delete(int $shopID)
    {
        // Check does it exist
        $exists = $this->dbCheck->existsInTable('ShopItems', 'ShopItemID', $shopID);
        if ($exists == false) {
            $response['code'] = 404;
            $response['contents'] = 'The ID does not exist';
            return $response;
        }

        $sql = $this->delete->delete()->from('ShopItems')->where('ShopItemID = :id')->result();
        $pdo = array('id' => $shopID);
        $this->database->run($sql, $pdo);

        $response['code'] = 204;
        $response['contents'] = array();

        return $response;
    }
}
