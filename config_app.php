<?php
define('DEBUG_FILE_NAME', $_SERVER['DOCUMENT_ROOT'].'/'.LIB_PATH.'/cn_log.txt');
define('CN_LOG', $_SERVER['DOCUMENT_ROOT'].'/'.LIB_PATH.'/cn_log.txt');

define('CLIENT_ID', 'app.5942780e6e27c0.89828187'); // like 'app.67efrrt2990977.85678329' or 'local.57062d3061fc71.97850406' - This code should take in a partner's site, needed only if you want to write a message from Bot at any time without initialization by the user
define('CLIENT_SECRET', 'jdHajsdZ5ib4I7acUsi3973k7B9xfbpazGNKNRrwWsSdQgGDkB'); // like '8bb00435c88aaa3028a0d44320d60339' - TThis code should take in a partner's site, needed only if you want to write a message from Bot at any time without initialization by the user

define('SQL_SERVER', 'b24go.mysql');
define('SQL_LOGIN', 'b24go_mysql');
define('SQL_PASSWORD', '84ow3veo');
define('SQL_TABLE', 'b24go_db');

define('ADMIN_SETTINGS', 'CN_SETTINGS');
define('ADMIN_SETTINGS_EL', 'CN_ADMIN_S');
define('CATALOG_CODE', 'CN_CATALOG');
define('CATALOG_CODE_EL', 'CN_CATALOG_EL');
define('BASKET_CODE', 'CN_BASKET');
define('ORDER_CODE', 'CN_ORDER');
define('WBOT_CODE', 'CN_WBOT');
define('WBOT_CODE_EL', 'CN_WBOT_EL');
define('CAT_DETAILS', 'CN_DETAILS');

$arPropsAdmins = array(
	array(
		'PROPERTY'=>'ADMINS_ID',
		'NAME'=>'Админы приложения',
		'TYPE'=>'S',
	),
	array(
		'PROPERTY'=>'LAST_ACTIVE_ADMIN_ID',
		'NAME'=>'Админ который работал с приложением ранее',
		'TYPE'=>'N',
	),
	array(
		'PROPERTY'=>'SETTINGS_STATUS',
		'NAME'=>'Статус настроек',
		'TYPE'=>'S',
	),
);

$arPropsCataloque = array(
	array(
		'PROPERTY'=>'PRODUCT_VOLUME',
		'NAME'=>'Объем продукта',
		'TYPE'=>'S',
	),
	array(
		'PROPERTY'=>'PRODUCT_PRICE',
		'NAME'=>'Цена продукта',
		'TYPE'=>'N',
	),
	array(
		'PROPERTY'=>'PRODUCT_IMG',
		'NAME'=>'Картинка продукта',
		'TYPE'=>'F',
	),
);

$arPropsBasket = array(
	array(
		'PROPERTY'=>'BASKET_USER_ID',
		'NAME'=>'Пользователь Б24',
		'TYPE'=>'S',
	),
	array(
		'PROPERTY'=>'BASKET_ITEM_ID',
		'NAME'=>'Товар каталога',
		'TYPE'=>'S',
	),
	array(
		'PROPERTY'=>'BASKET_ITEM_COUNT',
		'NAME'=>'Количество товара',
		'TYPE'=>'S',
	),
	array(
		'PROPERTY'=>'BASKET_ITEM_VOLUME',
		'NAME'=>'Объем товара',
		'TYPE'=>'S',
	),
	array(
		'PROPERTY'=>'BASKET_ITEM_PRICE',
		'NAME'=>'Цена товара',
		'TYPE'=>'S',
	),
	array(
		'PROPERTY'=>'BASKET_TYPE',
		'NAME'=>'Тип добавленного элемента',
		'TYPE'=>'S',
	),
);

$arPropsOrder = array(
	array(
		'PROPERTY'=>'ORDER_USER_ID',
		'NAME'=>'Пользователь Б24',
		'TYPE'=>'S',
	),
	array(
		'PROPERTY'=>'ORDER_ITEMS',
		'NAME'=>'Состав заказа',
		'TYPE'=>'S',
	),
	array(
		'PROPERTY'=>'ORDER_ADRESS',
		'NAME'=>'Адрес доставки',
		'TYPE'=>'S',
	),
	array(
		'PROPERTY'=>'ORDER_PHONE',
		'NAME'=>'Контактный телефон',
		'TYPE'=>'S',
	),
	array(
		'PROPERTY'=>'ORDER_STATUS',
		'NAME'=>'Статус',
		'TYPE'=>'S',
	),
);

$arPropsStatus = array();

if(array_key_exists('auth', $_REQUEST)) {

	$dialogId = $_REQUEST['data']['PARAMS']['DIALOG_ID'];
	$message = $_REQUEST['data']['PARAMS']['MESSAGE'];
	$messageId = $_REQUEST['data']['PARAMS']['MESSAGE_ID'];

	$botSettings = loadSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth']);
	$dialogSettings = loadSettings(WBOT_CODE, $dialogId, $_REQUEST['auth']);
	
}
