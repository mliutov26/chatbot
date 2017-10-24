<?php

$curWaterId = $dialogSettings['SETTINGS']['ID'];
$newWaterPrice = $command['COMMAND_PARAMS'];

$resItem = restCommand('entity.item.get', Array(
	'ENTITY' => CATALOG_CODE,
	'FILTER' => array(
		'ID' => $curWaterId,
	),
), $_REQUEST['auth']);

if (array_key_exists('error', $resItem) && !empty($resItem['error'])) {
	$editWaterMess = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Ой, что-то пошло не так. :( [br] Не удалось получить данные о товаре. [send=/exit_state]Завершить редактирование[/send]',
	), $_REQUEST['auth']);
	exit;
}

$item = current($resItem['result']);
$itemImg = $item['DETAIL_PICTURE'];
$itemName = $item['NAME'];

$itemPrice = $newWaterPrice;
$itemVolume = $item['PROPERTY_VALUES']['PRODUCT_VOLUME'];

// cnLog::Add('add_water $waterImgUrl: '.print_r($waterImgUrl, true));

imageGeneration(
	$itemImg,
	ROOT_PATH.'/outputPhoto.jpg', // output photo file
	ROOT_PATH.'/font/DroidSansMono.ttf', // ttf font file 1
	ROOT_PATH.'/font/PTSans.ttf', // ttf font file 2
	$itemName, // item name
	$itemPrice, // item price
	$itemVolume, // item V
	'#000' // font color
);

$urlImg = base64_encode(file_get_contents(ROOT_PATH.'/outputPhoto.jpg'));
$nameImg = urldecode(basename($itemImg));

$resEditPrice = restCommand('entity.item.update', Array(
	'ENTITY' => CATALOG_CODE,
	'ID' => $curWaterId,
	'PREVIEW_PICTURE' => array(
		$nameImg, $urlImg
	),
	'PROPERTY_VALUES' => array(
		'PRODUCT_PRICE' => $newWaterPrice,
	),
), $_REQUEST['auth']);

if (array_key_exists('error', $resEditPrice) && !empty($resEditPrice['error'])) {
	$editPriceMess = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Ой, что-то пошло не так. :( [br] Не удалось обновить цену товара. [send=/exit_state]Завершить редактирование[/send]',
	), $_REQUEST['auth']);

} else {

	$editPriceMess = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Цена товара обновлена. [send=/exit_state]Завершить редактирование[/send]',
	), $_REQUEST['auth']);
}
