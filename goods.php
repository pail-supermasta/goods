<?



/**
 * 
 */
class GoodsCustomerorder extends Customerorder{
	
	function __construct(){
		$this->goods_id = 'goods_id';
	}
}


/**
 * Класс для работы с Goods.ru
 */
class Goods{
	
	function __construct(){
		$this->token = "97B1BC55-189D-4EB4-91AF-4B9E9A985B3D";
		$this->api_url = "https://partner.goods.ru/api/market/v1/";
	}


	function curl($link, $_data){
		$data = array(
			"data" => array(
				"token" => $this->token,
			),
			"meta" => array(),
		);

		$headers = array(
			0 => "Content-Type: application/json",
		);

		$data['data'] = array_merge($data['data'], $_data);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->api_url.$link);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($curl);

    	if (curl_errno($curl)){
    		dump(curl_error());
    	} else {
    		$result = json_decode($result, true);
    	}

    	if ($result['success'] == '1'){
    		return $result;
    	} else {
    		dump('error');
    		dump($result);
    	}


	}


	function getOrdersAll(){
		$result = $this->curl('orderService/order/search', array(
		));
		return $result['data']['shipments'];
	}


	function getOrdersNew(){
		$result = $this->curl('orderService/order/search', array(
			"statuses" => array(
				0 => "NEW",
			)
		));
		return $result['data']['shipments'];;
	}


	function getOrdersConfirmed(){
		$result = $this->curl('orderService/order/search', array(
			"statuses" => array(
				0 => "CONFIRMED",
			)
		));
		return $result['data']['shipments'];;
	}


	function getOrdersPacked(){
		$result = $this->curl('orderService/order/search', array(
			"statuses" => array(
				0 => "PACKED",
			)
		));
		return $result['data']['shipments'];;
	}


	function getOrdersPackedExpired(){
		$result = $this->curl('orderService/order/search', array(
			"statuses" => array(
				0 => "PACKED_EXPIRED",
			)
		));
		return $result['data']['shipments'];;
	}


	function getOrdersPackedShipped(){
		$result = $this->curl('orderService/order/search', array(
			"statuses" => array(
				0 => "SHIPPED",
			)
		));
		return $result['data']['shipments'];;
	}


	function getOrdersPackedDelivered(){
		$result = $this->curl('orderService/order/search', array(
			"statuses" => array(
				0 => "DELIVERED",
			)
		));
		return $result['data']['shipments'];;
	}


	function getOrdersMerchantCanceled(){
		$result = $this->curl('orderService/order/search', array(
			"statuses" => array(
				0 => "MERCHANT_CANCELED",
			)
		));
		return $result['data']['shipments'];;
	}


	function getOrdersCustomerCanceled(){
		$result = $this->curl('orderService/order/search', array(
			"statuses" => array(
				0 => "CUSTOMER_CANCELED",
			)
		));
		return $result['data']['shipments'];;
	}


	function getOrder($id){
		$result = $this->curl('orderService/order/get', array(
			"shipments" => array(
				0 => $id,
			)
		));
		return $result['data']['shipments'][0];
	}


	function getOrders($ids){
		$result = $this->curl('orderService/order/get', array(
			"shipments" => $ids,
		));
		return $result['data']['shipments'];
	}


	function addCustomerorder($shipmentId){

		global $sql;

		$order = $this->getOrder($shipmentId);
		// dump($order);

		$put_data = array();
		$put_data['attributes'] = array();
		
		// Номер
		$put_data['name'] = $order['shipmentId'];
		// Без НДС
		$put_data['vatEnabled'] = false;
		// Не общий доступ
		$put_data['shared'] = false;
		// Проведено
		$put_data['applicable'] = true;
		// ООО «Новинки»
		$put_data['organization']['meta']['href'] = MS_PATH."/entity/organization/07bbe005-8b17-11e7-7a34-5acf0019232a";
		$put_data['organization']['meta']['type'] = "organization";
		// Покупатель Goods.ru
		$put_data['agent']['meta']['href'] = MS_PATH."/entity/counterparty/64710328-2e6f-11e8-9ff4-34e8000f81c8";
		$put_data['agent']['meta']['type'] = "counterparty";
		// Договор №Прод/КП-Н09.03.18 от 04.05.2018
		$put_data['contract']['meta']['href'] = MS_PATH."/entity/contract/f2f949de-4f79-11e8-9107-504800047057";
		$put_data['contract']['meta']['type'] = "contract";
		// Склад MP_NFF
		$put_data['store']['meta']['href'] = MS_PATH."/entity/store/48de3b8e-8b84-11e9-9ff4-34e8001a4ea1";
		$put_data['store']['meta']['type'] = "store";
		// #Логистика: агент — 1 Не нужна доставка
		$put_data['attributes'][0]['id'] = "4552a58b-46a8-11e7-7a34-5acf002eb7ad";
		$put_data['attributes'][0]['value']['meta']['href'] = MS_PATH."/entity/customentity/10f17383-95f9-11e6-7a69-9711000cd76f/6e64fdac-95f9-11e6-7a69-9711000cea11";
		$put_data['attributes'][0]['value']['meta']['type'] = "customentity";
		// ФИО
		$put_data['attributes'][1]['id'] = "5b766cb9-ef7e-11e6-7a31-d0fd001e5310";
		$put_data['attributes'][1]['value'] = $order['customerFullName'];
		// Адрес
		$put_data['attributes'][2]['id'] = "547ff930-ef8e-11e6-7a31-d0fd0021d13e";
		$put_data['attributes'][2]['value'] = $order['customerAddress'];
		// Источник
		$put_data['attributes'][3]['id'] = "e5c105a8-7c2d-11e6-7a31-d0fd00172c6f";
		$put_data['attributes'][3]['value']['meta']['href'] = MS_PATH."/entity/customentity/fdaebe54-7c2b-11e6-7a69-8f5500110acc/12e48a58-9e57-11e9-912f-f3d400070a00";
		$put_data['attributes'][3]['value']['meta']['type'] = "customentity";
		// Статус Новый
		$put_data['state']['meta']['href'] = MS_PATH."/entity/customerorder/metadata/states/327bfd05-75c5-11e5-7a40-e89700139935";
		$put_data['state']['meta']['type'] = "state";
		// Плановая дата отгрузки
		$put_data['deliveryPlannedMoment'] = date("Y-m-d H:i:s");
		$put_data['description'] = '';

		foreach ($order['items'] as $key => $item) {
			$product = $sql->query("SELECT * FROM `ms_product` WHERE `index` = '".$item['offerId']."' LIMIT 1")->row;
			if (!$product){
				$put_data['description'] .= "Внимание! Ошибка при сопоставлении товара! Проверьте заказ вручную!\n";
				continue;
			}

			$position = array();
			$position['quantity'] = $item['quantity'];
			$position['reserve'] = $item['quantity'];
			$position['price'] = $item['finalPrice'] * 100;
			$position['vat'] = 0;
			$position['assortment']['meta']['href'] = MS_PATH.'/entity/product/'.$product['id'];
			$position['assortment']['meta']['type'] = 'product';
			$put_data['positions'][] = $position;

			// Программа лояльности Гудс
					// $position['price'] = array_sum(array_column($item['discounts'], 'discountAmount')) * 100;
			if ($item['discounts']){
				foreach ($item['discounts'] as $key => $discount) {
					$position = array();
					$position['quantity'] = 1;
					$position['price'] = $discount['discountAmount'] * 100;
					$position['vat'] = 0;
					$position['assortment']['meta']['href'] = MS_PATH.'/entity/service/a3af6531-6fce-11e9-9109-f8fc0025a229';
					$position['assortment']['meta']['type'] = 'service';
					$put_data['positions'][] = $position;
					$put_data['description'] .= "Предоставлена скидка типа {$discount['discountType']} ({$discount['discountDescription']}) от goods на товар {$product['name']} в размере {$discount['discountAmount']} руб\n";
				}
			}

		}

		$result = curlMS(MS_PATH.'/entity/customerorder/', $put_data, 'POST');

		if (isset($result['errors'])){
			foreach ($result['errors'] as $key => $error) {
				telegram("<b>\xE2\x9A\xA0 GOODS:</b> Ошибка при обработке заказа №".$order['shipmentId']."\n".$error['error'], '-336297687');
			}
		} else {
			telegram("<b>\xE2\x9A\xA0 GOODS:</b> Добавлен заказ <a href=\"".$result['meta']['uuidHref']."\">№".$order['shipmentId']."</a>", '-336297687');
		}

		return $result;

	}

}

