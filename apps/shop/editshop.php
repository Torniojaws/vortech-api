<?php

namespace Apps\Shop;

class EditShop extends \Apps\Abstraction\CRUD
{
    public function edit(int $shopID, string $json)
    {
        if ($this->json->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        $data = json_decode($json, true);
        if (isset($data[0]) == false) {
            $data = array($data);
        }

        $this->editShopItem($shopID, $data[0]);
        $this->editCategories($shopID, $data[0]['categories']);
        $this->editUrls($shopID, $data[0]['urls']);

        $response['code'] = 200;
        $response['contents'] = 'Location: https://www.vortechmusic.com/api/1.0/shop/'.$shopID;

        return $response;
    }

    /**
     * Update the shopitem values to the new ones
     * @param int $shopID the ID we update
     * @param array $data has the new values
     */
    public function editShopItem(int $shopID, array $data)
    {
        $sql = $this->update->update('ShopItems')
            ->set('Title = :title, Description = :desc, Price = :price, Currency = :cur,
                Image = :img, Updated = NOW()')
            ->where('ShopItemID = :id')->result();
        $pdo = array(
            'title' => $data['title'],
            'desc' => $data['description'],
            'price' => $data['price']['value'],
            'cur' => $data['price']['currency'],
            'img' => $data['image'],
            'id' => $shopID
        );
        $this->database->run($sql, $pdo);
    }

    /**
     * Delete the old categories and then insert the new shopitem categories
     * @param int $shopID the ID we update
     * @param array $categories has the new values
     */
    public function editCategories(int $shopID, array $categories)
    {
        $sql = $this->delete->delete()->from('ShopItemCategories')->where('ShopItemID = :id')
            ->result();
        $pdo = array('id' => $shopID);
        $this->database->run($sql, $pdo);

        // Then insert the new values
        foreach ($categories as $category) {
            $sql = $this->create->insert()->into('ShopItemCategories(ShopItemID, ShopCategoryID)')
                ->values(':id, :cid')->result();
            $pdo = array('id' => $shopID, 'cid' => $category);
            $this->database->run($sql, $pdo);
        }
    }

    /**
     * Delete the old urls and insert the new ones. If ShopItemImageID is a string, a new picture
     * has been uploaded. We will insert that to the table ShopItemImages, and get the ID.
     * @param int $shopID the ID we update
     * @param array $data has the new values
     */
    public function editUrls(int $shopID, array $urls)
    {
        $sql = $this->delete->delete()->from('ShopItemURLs')->where('ShopItemID = :id')->result();
        $pdo = array('id' => $shopID);
        $this->database->run($sql, $pdo);

        // Insert the new data
        foreach ($urls as $url) {
            // A new picture was created, insert it to the DB and get the ID of it
            $shop = new \Apps\Shop\AddShop();
            $imageID = is_numeric($url['image']) ? $url['image'] : $shop->addNewURLImage($url['image']);

            $sql = $this->create->insert()->into('ShopItemURLs(ShopItemID, Title, URL, ShopItemImageID)')
                ->values(':id, :title, :url, :image')->result();
            $pdo = array(
                'id' => $shopID,
                'title' => $url['title'],
                'url' => $url['url'],
                'image' => $imageID
            );
            $this->database->run($sql, $pdo);
        }
    }
}
