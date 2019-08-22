<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12.08.2019
 * Time: 16:29
 */

namespace Avaks\Goods;


class Sticker
{
    private $state = false;


    /**
     * Печать и наклеивание Этикетки на каждое Грузовое место(запрос sticker/print
     */

    public function printPdf()
    {
//        продавец на проде 608 на тесте 1231
//        "boxCodes":['608*846882375*1']
        $data = '{
                    "shipmentId": "842818431",
                    "boxCodes":['1231*842818431*1']
                }';
        $data = json_decode($data, true);
        $pdf = Curl::curl('orderService/sticker/print', $data);


        if ($pdf) {
            $mpdf = new \Mpdf\Mpdf();
            $mpdf->WriteHTML($pdf['data']);
            if ($mpdf->Output('pdf/sticker-files/filename.pdf', \Mpdf\Output\Destination::FILE)) {
                return true;
            } else {
                return false;
            }
        }
    }


    public function glue()
    {

        return $this->state = true;
    }


}