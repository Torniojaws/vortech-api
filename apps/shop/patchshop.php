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

            foreach ($data as $column => $value) {
                switch ($column) {
                    case 'categories':
                        break;
                    case 'urls':
                        break;
                    case 'price':
                        break;
                    default:
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
}
