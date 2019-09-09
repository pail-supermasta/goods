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

    /**
     * @param $link
     * @param $token
     * @param $_data
     * @param bool $display
     * @return mixed
     */


//Amaze 97B1BC55-189D-4EB4-91AF-4B9E9A985B3D
//Фирдус C12405BF-01CB-4A6C-A41E-0E179EF00F54
    public static function execute($link,$token, $_data, $display = true)
    {

        $data = array(
                    "data" => array(
                        "token" => $token, // test-partner
                    ),
                    "meta" => array(),
                );
/*        $data = array(
            "data" => array(
                "token" => 'C12405BF-01CB-4A6C-A41E-0E179EF00F54',
            ),
            "meta" => array(),
        );*/

        $headers = array(
            0 => "Content-Type: application/json",
        );

        $data['data'] = array_merge($data['data'], $_data);

        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_URL, 'https://test-partner.goods.ru/api/market/v1/' . $link);
        curl_setopt($curl, CURLOPT_URL, 'https://partner.goods.ru/api/market/v1/' . $link);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);

        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

        if ($display == true) {
            echo "Post body is: \n" . json_encode($data) . "\n";
        }

        // For debugging

        curl_setopt($curl, CURLOPT_VERBOSE, 1);

        curl_setopt($curl, CURLOPT_FAILONERROR, false);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);



        $result = curl_exec($curl);
        $info = curl_getinfo($curl);


        if ($display == true) {
            print_r("\n" . $info['request_header']);
        }


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
            return $result;
        }


    }

}