<?php

$idEditableAccessory = $command['COMMAND_PARAMS'];

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
		'COMMAND_PARAMS' => 'add_accessories accessories accessories delivery discount refund fine admins water',
	),
	Array(
		'TEXT' => 'Сбросить все настройки',
		'BG_COLOR' => '#aeb1b7',
		'TEXT_COLOR' => '#f7f6f6',
		'DISPLAY' => 'LINE',
		'COMMAND' => 'reset_settings',
	),*/
);

$resEditAccessory = restCommand('entity.item.get', Array(
	'ENTITY' => CAT_DETAILS,
	'FILTER' => array(
		'ID' => $idEditableAccessory,
	),
), $_REQUEST['auth']);

if (array_key_exists('error', $resEditAccessory) && !empty($resEditAccessory['error'])) {
	$editAccessoryMess = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Ой, что-то пошло не так. :( [br] Не удалось получить данные о товаре',
		'KEYBOARD' => $keyboard,
	), $_REQUEST['auth']);

} else {

	$dialogSettings['SETTINGS']['STATE'] = 'edit_accessories';
	$dialogSettings['SETTINGS']['PARAM'] = 'none';
	$dialogSettings['SETTINGS']['ID'] = $idEditableAccessory;

	saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

	$curAccessory = current($resEditAccessory['result']);
	cnLog::Add('edit_Accessories curAccessories: '.print_r($curAccessory, true));

	$mess = 'Выберите [b]один из параметров[/b] для редактирования (или редактируйте по очереди каждый, нажимая enter): ';
	$mess .= '[put=/edit_accessories_name '.$curAccessory['NAME'].']название[/put], ';
	$mess .= '[send=/edit_accessories_image]изображение[/send], ';
	$mess .= '[put=/edit_accessories_price '.$curAccessory['PROPERTY_VALUES']['PRODUCT_PRICE'].']цена[/put]';

	$editAccessoriesMess = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => $mess,
		'KEYBOARD' => $keyboard,
	), $_REQUEST['auth']);
}
