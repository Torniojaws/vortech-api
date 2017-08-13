<?php

namespace Apps\Shop;

class PatchShop extends \Apps\Abstraction\CRUD
{
    public function patch(int $shopID, string $json)
    {
        if ($this->json->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        try {
            $data = json_decode($json, true);
            if (isset($data[0]) == false) {
                $data = array($data);
            }

            foreach ($data[0] as $column => $value) {
                switch ($column) {
                    case 'categories':
                        $this->updateCategories($shopID, $value);
                        break;
                    case 'urls':
                        $this->updateUrls($shopID, $value);
                        break;
                    case 'price':
                        $this->updatePrice($shopID, $value);
                        break;
                    default:
                        $this->updateShop($shopID, $column, $value);
                        break;
                }
            }
        } catch (\PDOException $ex) {
            $response['code'] = 400;
            $response['contents'] = 'Cannot patch data';
            return $response;
        }

        $response['code'] = 200;
        $response['contents'] = array();

        return $response;
    }

    /**
     * Replace the values in categories
     * @param int $shopID is the ID we update
     * @param int[] $categories are the new values
     */
    public function updateCategories(int $shopID, array $categories)
    {
        $sql = $this->delete->delete()->from('ShopItemCategories')->where('ShopItemID = :id')->result();
        $pdo = array('id' => $shopID);
        $this->database->run($sql, $pdo);

        foreach ($categories as $category) {
            $sql = $this->create->insert()->into('ShopItemCategories(ShopItemID, ShopCategoryID)')
                ->values(':id, :cid')->result();
            $pdo = array('id' => $shopID, 'cid' => $category);
            $this->database->run($sql, $pdo);
        }
    }

    /**
     * Replace the URLs for the current shop item. If the Image contains a string, it is a new
     * picture that was added during the patch. So we insert it to the ShopItemImages table.
     * @param int $shopID is the item we update
     * @param array $urls contains the values we update
     */
    public function updateUrls(int $shopID, array $urls)
    {
        // Does the same thing, so let's reuse it
        $shop = new \Apps\Shop\EditShop();
        $shop->editUrls($shopID, $urls);
    }

    /**
     * Update the price info of the ShopItem. The patch can also contain the currency.
     * @param int $shopID is the item we update
     * @param array $patch contains a set of values we update
     */
    public function updatePrice(int $shopID, array $patch)
    {
        foreach ($patch as $column => $value) {
            // Special case
            if ($column == 'value') {
                $column = 'Price';
            }

            $sql = $this->update->update('ShopItems')->set($column. ' = :value')
                ->where('ShopItemID = :id')->result();
            $pdo = array('value' => $value, 'id' => $shopID);
            $this->database->run($sql, $pdo);
        }
    }

    /**
     * Update a single column in the ShopItems table using the value we receive
     * @param int $shopID is the item we update
     * @param string $column is the column we update
     * @param mixed $value is the new value, which can be a string or int
     */
    public function updateShop(int $shopID, string $column, $value)
    {
        $sql = $this->update->update('ShopItems')->set($column. ' = :value')
            ->where('ShopItemID = :id')->result();
        $pdo = array('value' => $value, 'id' => $shopID);
        $this->database->run($sql, $pdo);
    }
}
