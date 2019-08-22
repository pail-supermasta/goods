<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 13.08.2019
 * Time: 13:07
 */

namespace Avaks\Goods;


class Curl
{
    /*		$this->token = "97B1BC55-189D-4EB4-91AF-4B9E9A985B3D";
            $this->api_url = "https://partner.goods.ru/api/market/v1/";*/

    public static function curl($link, $_data)
    {
        $data = array(
            "data" => array(
                "token" => "6881430B-882F-4C4F-8DCA-14FDAFEBAFEC",
            ),
            "meta" => array(),
        );

        $headers = array(
            0 => "Content-Type: application/json",
        );

        $data['data'] = array_merge($data['data'], $_data);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://test-partner.goods.ru/api/market/v1/' . $link);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);

        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        echo "Post body is: \n" . json_encode($data) . "\n";

        // For debugging

        curl_setopt($curl, CURLOPT_VERBOSE, 1);

        curl_setopt($curl, CURLOPT_FAILONERROR, false);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);


        $result = curl_exec($curl);
        $info = curl_getinfo($curl);


        print_r("\n" . $info['request_header']);


        if (curl_errno($curl)) {
//    		dump(curl_error());
            var_dump(curl_errno($curl) . PHP_EOL);
        } else {
            $result = json_decode($result, true);
        }

        if ($result['success'] == '1') {
            return $result;
        } else {
            /*    		dump('error');
                        dump($result);*/
            var_dump('error');
            var_dump($result);
        }


    }

}