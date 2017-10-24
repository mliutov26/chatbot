<?php

$idEditableWater = $command['COMMAND_PARAMS'];

$keyboard = Array(
    Array('TYPE' => 'NEWLINE'),
    Array(
        'TEXT' => 'Доступные команды',
        'BG_COLOR' => '#e8e9eb',
        'TEXT_COLOR' => '#333',
        'DISPLAY' => 'LINE',
        'COMMAND' => 'settings',
    ),
	/*Array(
		'TEXT' => 'Доступные команды',
		'BG_COLOR' => '#e8e9eb',
		'TEXT_COLOR' => '#333',
		'DISPLAY' => 'LINE',
		'COMMAND' => 'available_commands',
		'COMMAND_PARAMS' => 'add_water water accessories delivery discount refund fine admins',
	),
	Array(
		'TEXT' => 'Сбросить все настройки',
		'BG_COLOR' => '#aeb1b7',
		'TEXT_COLOR' => '#f7f6f6',
		'DISPLAY' => 'LINE',
		'COMMAND' => 'reset_settings',
	),*/
);

$resEditWater = restCommand('entity.item.get', Array(
	'ENTITY' => CATALOG_CODE,
	'FILTER' => array(
		'ID' => $idEditableWater,
	),
), $_REQUEST['auth']);

if (array_key_exists('error', $resEditWater) && !empty($resEditWater['error'])) {
	$editWaterMess = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Ой, что-то пошло не так. :( [br] Не удалось получить данные о товаре',
		'KEYBOARD' => $keyboard,
	), $_REQUEST['auth']);

} else {

	$dialogSettings['SETTINGS']['STATE'] = 'edit_water';
	$dialogSettings['SETTINGS']['PARAM'] = 'none';
	$dialogSettings['SETTINGS']['ID'] = $idEditableWater;

	saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

	$curWater = current($resEditWater['result']);

	$mess = 'Выберите [b]один из параметров[/b] для редактирования (или редактируйте по очереди каждый, нажимая enter): ';
	$mess .= '[put=/edit_water_name '.$curWater['NAME'].']название[/put], ';
	$mess .= '[send=/edit_water_image]изображение[/send], ';
	$mess .= '[put=/edit_water_volume '.$curWater['PROPERTY_VALUES']['PRODUCT_VOLUME'].']объем[/put], ';
	$mess .= '[put=/edit_water_price '.$curWater['PROPERTY_VALUES']['PRODUCT_PRICE'].']цена[/put]';

	$editWaterMess = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => $mess,
		'KEYBOARD' => $keyboard,
	), $_REQUEST['auth']);
}
