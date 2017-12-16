<?php

namespace common\helpers;
use Yii;
use Coinbase\Wallet\Client;
use Coinbase\Wallet\Configuration;
use Coinbase\Wallet\Resource\Address;

/**
 * CoinbaseHelper
 *
 */
class CoinbaseHelper
{
    
    public $client;
    public $configuration;
    
    public function __construct()
    {
        $apiKey = Yii::$app->keyStorage->get('coin.apiKey');
        $apiSecret = Yii::$app->keyStorage->get('coin.apiSecret');
        $this->configuration = Configuration::apiKey($apiKey, $apiSecret);
        //$this->configuration->setLogger($logger);
        $this->client = Client::create($this->configuration);
    }

    /**
     * 
     */
    public function rate($currency = 'BTC')
    {
        // $apiKey = Yii::$app->keyStorage->get('coin.apiKey');
        // $apiSecret = Yii::$app->keyStorage->get('coin.apiSecret');
        // $configuration = Configuration::apiKey($apiKey, $apiSecret);
        // $configuration->setLogger($logger);
        // $client = Client::create($configuration);
        //$client = CoinbaseHelper::configClient();
        
        $spotPrice = $this->client->getSpotPrice('BTC-USD');
        return $spotPrice;
        
    }
    public static function fetchRate($currency = 'BTC')
    {
        //https://api.coinbase.com/v2/exchange-rates?currency=BTC
        //http://www.coloring.ws/pandas.htm
        
        $url = 'https://api.coinbase.com/v2/exchange-rates?currency='.$currency;

        $cURL = curl_init();
        curl_setopt($cURL, CURLOPT_URL, $url);
        curl_setopt($cURL, CURLOPT_HTTPGET, true);
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);
        
        curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json',
            'CB-VERSION : 2017-11-29'
        ));
        
        $result = curl_exec($cURL);
        curl_close($cURL);
        $json = json_decode($result, true);
        return $json['data']['rates']['USD'];
    }
    
    public static function fetchPriceUsd($currency = 'USD')
    {
        //https://api.coinbase.com/v2/prices/spot?currency=USD
        //http://www.coloring.ws/pandas.htm
        
        $url = 'https://api.coinbase.com/v2/prices/spot?currency='.$currency;

        $cURL = curl_init();
        curl_setopt($cURL, CURLOPT_URL, $url);
        curl_setopt($cURL, CURLOPT_HTTPGET, true);
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);
        
        curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json',
            'CB-VERSION : 2017-11-29'
        ));
        
        $result = curl_exec($cURL);
        curl_close($cURL);
        //var_dump($result);
        //die;
        $json = json_decode($result, true);
        return $json['data']['amount'];
    }
    
    public function createAddress($type = null)
    {
        $user = Yii::$app->user->identity;
        $accounts = $this->client->getAccounts();
        $addresses = [];
        foreach($accounts->all() as $account){
            if($type == null){
                $address = new Address(['name' => $account->getCurrency().' - '.$user->username]);
                $this->client->createAccountAddress($account, $address);
                $addresses[$account->getCurrency()] = $address;
            }else{
                if($type == $account->getCurrency()){
                    $address = new Address(['name' => $account->getCurrency().' - '.$user->username]);
                    $this->client->createAccountAddress($account, $address);
                    $addresses[$type] = $address;
                }else{
                    break;
                }
            }
        }
        return $addresses;
    }
    
    public function createWalletCoin(){
        $length = 35;
        $bytes = Yii::$app->getSecurity()->generateRandomKey($length);
        return "T".StringHelper::generateRandomString($bytes,$length);
    }
}
