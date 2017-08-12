<?php

namespace Apps\Shop;

class AddShop extends \Apps\Abstraction\CRUD
{
    public function add(string $json)
    {
        if ($this->json->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            $response['id'] = -1;
            return $response;
        }

        $shopitem = json_decode($json, true);
        if (isset($shopitem[0]) == false) {
            // Support both JSON Objects and JSON Arrays by converting Object to an Array
            $shopitem = array($shopitem);
        }

        // Insert the ShopItem first to get the ID that is used as foreign key in the other tables
        $shopItemID = $this->addShopItem($shopitem[0]);

        foreach ($shopitem[0] as $column => $value) {
            switch ($column) {
                case 'categories':
                    $this->addCategories($shopItemID, $value);
                    break;
                case 'urls':
                    $this->addUrls($shopItemID, $value);
                    break;
                default:
                    // We have already inserted the other keys before this part, so skip them
                    break;
            }
        }

        $response['code'] = 201;
        $response['contents'] = 'Location: https://www.vortechmusic.com/api/1.0/shop/'.$shopItemID;
        $response['id'] = $shopItemID;

        return $response;
    }

    /**
     * Insert the specified values into ShopItems table and return the ID that we need in the other
     * inserts as a foreign key.
     * @param array $data contains all the values. Any keys that are not defined below are ignored.
     * @return int $shopItemID is the ID of the insert, which will be used as foreign key
     */
    public function addShopItem(array $data)
    {
        $sql = $this->create->insert()
            ->into('ShopItems(Title, Description, Price, Currency, Image, Created)')
            ->values(':title, :desc, :price, :currency, :img, NOW()')->result();
        $pdo = array(
            'title' => $data['title'],
            'desc' => $data['description'],
            'price' => $data['price']['value'],
            'currency' => $data['price']['currency'],
            'img' => $data['image']
        );
        $this->database->run($sql, $pdo);

        return $this->database->getInsertId();
    }

    /**
     * We add the shop item categories here. The array is an int array, where the value refers to
     * the predefined ID in ShopCategories table.
     * @param int $shopItemID is the foreign key to the Shop Item the categories here are for
     * @param int[] $categories are the IDs we refer to
     */
    public function addCategories(int $shopItemID, array $categories)
    {
        foreach ($categories as $category) {
            $sql = $this->create->insert()->into('ShopItemCategories(ShopItemID, ShopCategoryID)')
                ->values(':sid, :cid')->result();
            $pdo = array('sid' => $shopItemID, 'cid' => $category);
            $this->database->run($sql, $pdo);
        }
    }

    /**
     * We add the URLs and titles for a given shop item. If the key 'image' is a string, it means
     * we have received a new image during the add process and we should create the DB entry for
     * that file. If the key 'image' is an integer, it refers to an existing ID in ShopItemImages
     * table.
     * @param int $shopItemID is the item we refer to
     * @param mixed[] $urls contains either the ID of an image, or the filename of a new one
     */
    public function addUrls(int $shopItemID, array $urls)
    {
        foreach ($urls as $url) {
            $imageID = is_numeric($url['image']) ? $url['image'] : $this->addNewURLImage($url['image']);
            $sql = $this->create->insert()
                ->into('ShopItemURLs(ShopItemID, Title, URL, ShopItemImageID)')
                ->values(':id, :title, :url, :img')->result();
            $pdo = array(
                'id' => $shopItemID,
                'title' => $url['title'],
                'url' => $url['url'],
                'img' => $imageID
            );
            $this->database->run($sql, $pdo);
        }
    }

    /**
     * Add a new entry to ShopItemImages and return the ID of it. If filename is identical to some
     * some existing entry, we do *NOT* insert it. This is usually when the user updates an existing
     * image, so it is fine to keep the same filename. It's probably unlikely that we'll keep two
     * different images for the same party.
     * @param string $filename is the filename of the new image that was uploaded
     * @return int $imageID is the ID of the inserted image
     */
    public function addNewURLImage(string $filename)
    {
        // Check if it exists in the DB, and if so, get the ID
        $sql = $this->read->select('ShopItemImageID')->from('ShopItemImages')
            ->where('Image = :img')->limit(1)->result();
        $pdo = array('img' => $filename);
        $result = $this->database->run($sql, $pdo);
        $imageID = isset($result[0]) ? intval($result[0]['ShopItemImageID']) : 0;

        // If no image was found, we add it
        if ($imageID == 0) {
            $sql = $this->create->insert()->into('ShopItemImages(Image, Created)')
                ->values(':img, NOW()')->result();
            $pdo = array('img' => $filename);
            $this->database->run($sql, $pdo);
            $imageID = $this->database->getInsertId();
        }

        return $imageID;
    }
}
