<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 13.08.2019
 * Time: 13:07
 */

namespace Avaks\MS;


class CurlMoiSklad
{


    public static function curlMS($link, $data = false)
    {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_URL, 'https://online.moysklad.ru/api/remap/1.1/' . $link);
        curl_setopt($curl, CURLOPT_HTTPGET, true);
        curl_setopt($curl, CURLOPT_USERPWD, 'kurskii@техтрэнд:UR4638YFe');


        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        if ($data) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        if ($data) {
            $headers[] = 'Content-Length:' . strlen(json_encode($data));
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($curl);
        $curl_errno = curl_errno($curl);
        curl_close($curl);

        if ($curl_errno == 0) {
            return $result;
        } else {
            return $curl_errno;
        }

    }
}

