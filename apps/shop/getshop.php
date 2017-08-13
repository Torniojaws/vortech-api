<?php

namespace Apps\Shop;

class GetShop extends \Apps\Abstraction\CRUD
{
    /**
     * Get the data of shop items.
     * @param int $shopID is given if you want to retrieve a specific shop item
     * @param array $filters allows you to filter the results when getting all items
     * @return array $response has the results
     */
    public function get(int $shopID = null, array $filters = null)
    {
        $result = empty($shopID) ? $this->getAllItems($filters) : $this->getOneItem($shopID);

        $response['code'] = 200;
        $response['contents'] = $result;

        return $response;
    }

    /**
     * Build the response using the various tables we search from
     * @param int $shopID is the ID we use
     * @return array $result contains all the data we want to include
     */
    public function getOneItem(int $shopID)
    {
        $sql = $this->read->select()->from('ShopItems')->where('ShopItemID = :id')->result();
        $pdo = array('id' => $shopID);
        $shopitem = $this->database->run($sql, $pdo)[0];

        // Build the array, which will be converted to a JSON later on
        $result['title'] = $shopitem['Title'];
        $result['description'] = $shopitem['Description'];
        $result['price'] = $shopitem['Price'];
        $result['currency'] = $shopitem['Currency'];
        $result['image'] = $shopitem['Image'];
        $result['availableSince'] = $shopitem['Created'];
        $result['updated'] = $shopitem['Updated'];
        $result['categories'] = $this->getCategories($shopID);
        $result['urls'] = $this->getUrls($shopID);

        return $result;
    }

    /**
     * Return the data of all DB items in an array of arrays
     * @param array $filters allows you to optionally filter the results
     * @return array $allItems all results in the format we want
     */
    public function getAllItems(array $filters = null)
    {
        $sql = $this->read->select('ShopItemID')->from('ShopItems')->order('ShopItemID ASC')->result();
        $pdo = array();

        // User wants to have specific filters in the search
        if (empty($filters) == false) {
            // Only one filter is supported for now, so we check the 0-index
            switch (array_keys($filters)[0]) {
                case 'category':
                    // eg. "Releases", "Clothing", "Boxsets" - the query is case-insensitive
                    $sql = $this->read
                        ->select('DISTINCT(s.ShopItemID)')
                        ->from('ShopItems s, ShopItemCategories sc')
                        ->where('s.ShopItemID = sc.ShopItemID
                            AND sc.ShopCategoryID IN (
                                SELECT ShopCategoryID
                                FROM ShopCategories
                                WHERE Category = :category
                            )')
                        ->result();
                    $pdo = array('category' => $filters['category']);
                    break;
                default:
                    // No action, we will return all results as if no filter was used
                    break;
            }
        }

        $results = $this->database->run($sql, $pdo);

        $allItems = array();
        if (empty($results) == false) {
            $shopitemIDs = $this->arrays->flattenArray($results, 'ShopItemID');
            foreach ($shopitemIDs as $shopitemID) {
                $allItems[] = $this->getOneItem($shopitemID);
            }
        }

        return $allItems;
    }

    /**
     * Get the Category IDs for the current shopitem and return it as a flat array of integers
     * @param int $shopID is the ID we check
     * @return int[] $categories has the results
     */
    public function getCategories(int $shopID)
    {
        $sql = $this->read->select('ShopCategoryID')->from('ShopItemCategories')
            ->where('ShopItemID = :id')->order('ShopCategoryID ASC')->result();
        $pdo = array('id' => $shopID);
        $result = $this->database->run($sql, $pdo);

        return $this->arrays->flattenArray($result, 'ShopCategoryID');
    }

    /**
     * Iterate over all the urls associated with the shop item. For example the same item will
     * probably have multiple external urls, like Spotify and PayPal.
     * @param int $shopID is the ID of the shopitem
     * @return array $results contains the data for the current shopitem
     */
    public function getUrls(int $shopID)
    {
        $sql = $this->read->select('Title, URL, ShopItemImageID')->from('ShopItemURLs')
            ->where('ShopItemID = :id')->order('ShopItemID ASC')->result();
        $pdo = array('id' => $shopID);
        $urls = $this->database->run($sql, $pdo);

        $result = array();
        // There can be several...
        foreach ($urls as $url) {
            $current['title'] = $url['Title'];
            $current['url'] = $url['URL'];
            $current['imageID'] = $url['ShopItemImageID'];
            $result[] = $current;
        }

        return $result;
    }
}
