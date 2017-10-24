<?php

$curAccessoryId = $dialogSettings['SETTINGS']['ID'];
$newAccessoryName = $command['COMMAND_PARAMS'];

$resItem = restCommand('entity.item.get', Array(
	'ENTITY' => CAT_DETAILS,
	'FILTER' => array(
		'ID' => $curAccessoryId,
	),
), $_REQUEST['auth']);

if (array_key_exists('error', $resItem) && !empty($resItem['error'])) {
	$editAccessoryMess = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Ой, что-то пошло не так. :( [br] Не удалось получить данные о товаре. [send=/exit_state]Завершить редактирование[/send]',
	), $_REQUEST['auth']);
	exit;
}

$item = current($resItem['result']);
$itemImg = $item['DETAIL_PICTURE'];
$itemPrice = $item['PROPERTY_VALUES']['PRODUCT_PRICE'];

imageGeneration(
	$itemImg,
	ROOT_PATH.'/outputPhoto.jpg', // output photo file
	ROOT_PATH.'/font/DroidSansMono.ttf', // ttf font file 1
	ROOT_PATH.'/font/PTSans.ttf', // ttf font file 2
	$newAccessoryName, // item name
	$itemPrice, // item price
	$itemVolume, // item V
	'#000' // font color
);

$urlImg = base64_encode(file_get_contents(ROOT_PATH.'/outputPhoto.jpg'));
$nameImg = urldecode(basename($itemImg));

$resEditAccessoryName = restCommand('entity.item.update', Array(
	'ENTITY' => CAT_DETAILS,
	'ID' => $curAccessoryId,
	'NAME' => $newAccessoryName,
	'PREVIEW_PICTURE' => array(
		$nameImg, $urlImg
	)
), $_REQUEST['auth']);

if (array_key_exists('error', $resEditAccessoryName) && !empty($resEditAccessoryName['error'])) {
	$editAccessoryMess = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Ой, что-то пошло не так. :( [br] Не удалось обновить название аксессуара. [send=/exit_state]Завершить редактирование[/send]',
	), $_REQUEST['auth']);

} else {

	$editAccessoryMess = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Название аксессуара обновлено. [send=/exit_state]Завершить редактирование[/send]',
	), $_REQUEST['auth']);
}
