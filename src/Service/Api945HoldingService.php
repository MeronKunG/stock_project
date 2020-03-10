<?php

namespace App\Service;

use Symfony\Component\Cache\Adapter\AdapterInterface;

class Api945HoldingService
{
    private $apiKey = "XbOiHrrpH8aQXObcWj69XAom1b0ac5eda2b";

    private $cacheKeyPrefix = "Api945HoldingService";
    private $cache;

    public function __construct(AdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getServiceVersion()
    {
        return [
            "version" => "1.0",
            "versionNumber" => 1000
        ];
    }

//    public function listAllProduct(int $merId = 1)
//    {
//        $cache_key = $this->cacheKeyPrefix . ".listAllProduct.merId." . $merId . ".all";
//        $cache_lifetime = 300;
//        $cache_item = $this->cache->getItem($cache_key);
//        if ($cache_item->isHit()) {
//            $output = $cache_item->get();
//            return $output;
//        }
//        $output = [
//            "paging" => [],
//            "result" => []
//        ];
//        $start = 0;
//        $step = 100;
//        $all_rowcount = 0;
//        do {
//            $data = $this->listProduct($merId, $start);
//            $output['paging'] = $data['paging'];
//
//            $step = $data['paging']['current_limitend'];
//            $output['result'] = array_merge($output['result'],  $data['result']);
//            $start += $step;
//            $all_rowcount = $data['paging']['all_rowcount'];
//        } while ($start < $all_rowcount);
//        $output['paging']['current_limitstart'] = 0;
//        $output['paging']['current_limitend'] = count($output['result']);
//
//        $cache_item->set($output);
//        $cache_item->expiresAfter($cache_lifetime);
//        $this->cache->save($cache_item);
//        return $output;
//    }

    public function listProduct(int $merId = 1, int $start = 0)
    {

        $cache_key = $this->cacheKeyPrefix . ".listProduct.merId." . $merId . ".start." . $start;
        $cache_lifetime = 15;
        $cache_item = $this->cache->getItem($cache_key);
        if ($cache_item->isHit()) {
            $output = $cache_item->get();
            return $output;
        }

        $url = "https://www.945holding.com/webservice/restful/merchant/v11/getproduct";


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.945holding.com/webservice/restful/merchant/v11/getproduct",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query([
                "apikey" => $this->apiKey,
                "merid" => $merId,
                "start" => $start
            ]),
            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
                "Content-Type: application/x-www-form-urlencoded",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return $err;
        } else {
            $result = json_decode($response, true);
            $cache_item->set($result);
            $cache_item->expiresAfter($cache_lifetime);
            $this->cache->save($cache_item);
            return $result;
        }
    }

    public function listAllProduct()
    {

        $cache_key = $this->cacheKeyPrefix . ".listAllProduct";
        $cache_lifetime = 15;
        $cache_item = $this->cache->getItem($cache_key);
        if ($cache_item->isHit()) {
            $output = $cache_item->get();
            return $output;
        }

        $url = "https://www.945holding.com/webservice/restful/merchant/v11/getallproduct";


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.945holding.com/webservice/restful/merchant/v11/getallproduct",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query([
                "apikey" => $this->apiKey
            ]),
            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
                "Content-Type: application/x-www-form-urlencoded",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return $err;
        } else {
            $result = json_decode($response, true);
            $cache_item->set($result);
            $cache_item->expiresAfter($cache_lifetime);
            $this->cache->save($cache_item);
            return $result;
        }
    }
}