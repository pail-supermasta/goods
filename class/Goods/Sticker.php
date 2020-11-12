<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12.08.2019
 * Time: 16:29
 */

namespace Avaks\Goods;


use Mpdf\MpdfException;

class Sticker
{
    private $state = false;


    /**
     * Печать и наклеивание Этикетки на каждое Грузовое место(запрос sticker/print
     * @param $shipmentId
     * @return bool
     */

    public function printPdf($shipmentId,$shopToken, $boxCode)
    {
        $toReturn = false;
        $data = '{
                    "shipmentId": "' . $shipmentId . '",
                    "boxCodes":["' . $boxCode . '*' . $shipmentId . '*1"]
                }';
        $data = json_decode($data, true);
        $pdf = Curl::execute('orderService/sticker/print',$shopToken, $data);



        if ($pdf) {
            try {
                $mpdf = new \Mpdf\Mpdf(['mode' => '',
                    'format' => [70,100],
                    'default_font_size' => 0,
                    'default_font' => '',
                    'margin_left' => 0,
                    'margin_right' => 0,
                    'margin_top' => 0,
                    'margin_bottom' => 0,
                    'margin_header' => 0,
                    'margin_footer' => 0,
                    'orientation' => 'L']);
            } catch (MpdfException $e) {
                error_log($e . " \n", 3, "printPdf_errors.log");
            }


            try {
                $mpdf->WriteHTML($pdf['data']);
            } catch (MpdfException $e) {
                error_log($e . " \n", 3, "printPdf_errors.log");
            }


            try {
//                если надо сохранить в файл
                /*if ($mpdf->Output('pdf/sticker-files/Маркировка ' . $shipmentId . '.pdf', \Mpdf\Output\Destination::FILE)) {
                    $toReturn = true;
                } else {
                    $toReturn = false;
                }*/
//                вернуть строкой
                return $mpdf->Output(null, \Mpdf\Output\Destination::STRING_RETURN);
            } catch (MpdfException $e) {
                error_log($e . " \n", 3, "printPdf_errors.log");
            }


        }
        return $toReturn;
    }


}