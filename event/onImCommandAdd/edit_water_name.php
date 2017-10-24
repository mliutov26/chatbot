<?php
//cnLog::Add('edit_water_name $command[COMMAND_PARAMS]: '.print_r($command['COMMAND_PARAMS'], true));
$curWaterId = $dialogSettings['SETTINGS']['ID'];
$newWaterName = $command['COMMAND_PARAMS'];

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

$itemPrice = $item['PROPERTY_VALUES']['PRODUCT_PRICE'];
$itemVolume = $item['PROPERTY_VALUES']['PRODUCT_VOLUME'];

// cnLog::Add('edit water name $itemImg: '.print_r($itemImg, true));
// cnLog::Add('edit water name $newWaterName: '.print_r($newWaterName, true));
// cnLog::Add('edit water name $itemPrice: '.print_r($itemPrice, true));
// cnLog::Add('edit water name $itemVolume: '.print_r($itemVolume, true));

imageGeneration(
	$itemImg,
	ROOT_PATH.'/outputPhoto.jpg', // output photo file
	ROOT_PATH.'/font/DroidSansMono.ttf', // ttf font file 1
	ROOT_PATH.'/font/PTSans.ttf', // ttf font file 2
	$newWaterName, // item name
	$itemPrice, // item price
	$itemVolume, // item V
	'#000' // font color
);

$urlImg = base64_encode(file_get_contents(ROOT_PATH.'/outputPhoto.jpg'));
$nameImg = urldecode(basename($itemImg));

//cnLog::Add('edit water name $urlImg: '.print_r($urlImg, true));
//cnLog::Add('edit water name $nameImg: '.print_r($nameImg , true));

$resEditName = restCommand('entity.item.update', Array(
	'ENTITY' => CATALOG_CODE,
	'ID' => $curWaterId,
	'NAME' => $newWaterName,
	'PREVIEW_PICTURE' => array(
		$nameImg, $urlImg
	)
), $_REQUEST['auth']);

if (array_key_exists('error', $resEditName) && !empty($resEditName['error'])) {
	//cnLog::Add('edit water error $resEditName: '.print_r($resEditName , true));
	$editWaterMess = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Ой, что-то пошло не так. :( [br] Не удалось обновить название товара. [send=/exit_state]Завершить редактирование[/send]',
	), $_REQUEST['auth']);

} else {
	//cnLog::Add('edit water name $resEditName: '.print_r($resEditName , true));
	$editWaterMess = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Название товара обновлено. [send=/exit_state]Завершить редактирование[/send]',
	), $_REQUEST['auth']);
}
