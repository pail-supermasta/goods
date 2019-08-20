<?

error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once __DIR__.'/../engine/core.php';
require_once __DIR__.'/../engine/moysklad.php';
require_once __DIR__.'/../engine/goods.php';

$goods = new Goods();
$ms = new MoySklad();

dump($goods);
dump($ms);

// dump($goods->getOrder('942190884'));
$goods_order_names = $goods->getOrdersNew();
$message = "Goods.ru (".count($goods_order_names).")\n";
	foreach ($goods_order_names as $key => $goods_order_name) {
		if ($customerorder = $ms->getCustomerorderByName($goods_order_name)){
			$message.= 'Заказ №'.$customerorder['name']." уже существует в МС\n";
	} else {
		$customerorder = $goods->addCustomerorder($goods_order_name);
		$message.= 'Заказ №'.$customerorder['name']." создан\n";
	}
}

echo $message;
telegram($message);