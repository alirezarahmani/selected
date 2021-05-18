<?php

namespace App\Service;



use GuzzleHttp\Client;

use SendinBlue\Client\Api\AccountApi;
use SendinBlue\Client\Api\TransactionalSMSApi;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Model\SendTransacSms;

class SMS
{
    public static function getAccount()
    {
        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $_ENV['SENDING_BLUE_V3']);

        $apiInstance = new AccountApi(
        // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
        // This is optional, `GuzzleHttp\Client` will be used as default.
            new Client(),
            $config
        );

        try {
            $result = $apiInstance->getAccount();
            print_r($result);die;
        } catch (\Exception $e) {
            echo 'Exception when calling AccountApi->getAccount: ', $e->getMessage(), PHP_EOL;die;
        }

    }

    public static function sendSms($code,$number)
    {

        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $_ENV['SENDING_BLUE_V3']);

        $apiInstance = new TransactionalSMSApi(
        // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
        // This is optional, `GuzzleHttp\Client` will be used as default.
            new Client(),
            $config
        );
        $data=[
            'sender'=>'Time',
            'recipient'=>$number,
            'content'=>'Your code is '.$code,
            'type'=>'transactional',
            'tag'=>'selected-time',
        ];
        $sendTransacSms = new SendTransacSms($data); // \SendinBlue\Client\Model\SendTransacSms | Values to send a transactional SMS

        try {
            $result = $apiInstance->sendTransacSms($sendTransacSms);

        } catch (\Exception $e) {
            echo 'Exception when calling AccountApi->getAccount: ', $e->getMessage(), PHP_EOL;die;
        }

    }



    protected function getHeaders()
    {
        return [
            'headers' => [
                'api-key'      => $_ENV['SENDING_BLUE_V3'],
                'Content-Type' => 'application/json'
            ]
        ];
    }

}
