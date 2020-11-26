<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 25.08.2020
 * Time: 12:19
 */

namespace Avaks;


class BackendAPI
{
    public $urlLogin;
    public $urlOrder;
    public $urlProduct;
    public $urlStock;
    public $userData;
    public $token;

    public function __construct()
    {
        $this->urlLogin = 'https://api.backendserver.ru/api/v1/auth/login';
        $this->urlOrder = 'https://api.backendserver.ru/api/v1/customerorder';
        $this->urlProduct = 'https://api.backendserver.ru/api/v1/product';
        $this->urlBundle = 'https://api.backendserver.ru/api/v1/bundle';
        $this->urlService = 'https://api.backendserver.ru/api/v1/service';
        $this->urlStock = 'https://api.backendserver.ru/api/v1/report_stock_all';
        $this->userData = array("username" => "mongodb@техтрэнд", "password" => "!!@th9247t924");
        $this->token = "bW9uZ29kYkDRgtC10YXRgtGA0Y3QvdC0OiEhQHRoOTI0N3Q5MjQ=";

    }

    private function getToken()
    {
        $ch = curl_init($this->urlLogin);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->userData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $res = curl_exec($ch);
        $result = json_decode($res, true);
        curl_close($ch);
        return $result['token'];
    }

    public function getData($urlProduct, $data)
    {
        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            sprintf('Authorization: Bearer %s', $this->token)
        );

        $data_string = http_build_query($data);

        $ch = curl_init($urlProduct);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_URL, $urlProduct . '/?' . $data_string.'&token=bW9uZ29kYkDRgtC10YXRgtGA0Y3QvdC0OiEhQHRoOTI0N3Q5MjQ=');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $res = curl_exec($ch);
        $result = json_decode($res, true);
        curl_close($ch);

        return $result;
    }

    public function getQuantity($urlProduct, $token)
    {

        $data['limit'] = 999999;
        $data['offset'] = 0;
        $data['project'] = json_encode(array(
                '_id' => true,
                '_product' => true,
                'quantity' => true,
                'reserve' => true,
                'stock' => true,
                'updated' => true
            )
        );

        $data['filter'] = json_encode(array('_store' => '48de3b8e-8b84-11e9-9ff4-34e8001a4ea1'));

        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            sprintf('Authorization: Bearer %s', $token)
        );

        $data_string = http_build_query($data);
        $ch = curl_init($urlProduct);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_URL, $urlProduct . '/?' . $data_string);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $res = curl_exec($ch);
        $result = json_decode($res, true);
        curl_close($ch);

        return $result;
    }
}