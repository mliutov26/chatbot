<?php

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
		'COMMAND_PARAMS' => 'water accessories discount delivery refund fine admins hello',
	),
	Array(
		'TEXT' => 'Сбросить все настройки',
		'BG_COLOR' => '#aeb1b7',
		'TEXT_COLOR' => '#f7f6f6',
		'DISPLAY' => 'LINE',
		'COMMAND' => 'reset_settings',
	),*/
);

if($command['COMMAND_PARAMS'] == 'add') {
	$botSettings['SETTINGS']['DISCOUNT'] = array();
	saveSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth'], $botSettings['SETTINGS']);

	$dialogSettings['SETTINGS']['STATE'] = 'add_discount';
	$dialogSettings['SETTINGS']['PARAM'] = 'discount_summ';
	saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

	$resHello = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Введите пороговую сумму скидки в рублях:',
	), $_REQUEST['auth']);
	exit;
}

if($command['COMMAND_PARAMS'] == 'delete') {
	$botSettings['SETTINGS']['DISCOUNT'] = '';
	saveSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth'], $botSettings['SETTINGS']);

	$dialogSettings['SETTINGS'] = array(
		'STATE' => '',
		'PARAM' => '',
		'ID' => ''
	);
	saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

	$resHello = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Скидка удалена. [send=/discount add]Добавить[/send]',
		'KEYBOARD' => $keyboard
	), $_REQUEST['auth']);
	exit;
}

if(!array_key_exists('DISCOUNT', $botSettings['SETTINGS']) || empty($botSettings['SETTINGS']['DISCOUNT'])) {
	$resDelivery = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Данных для расчета скидки пока нет. [send=/discount add]Добавить информацию[/send]',
	), $_REQUEST['auth']);
	exit;
}

$arrDiscount = (is_array($botSettings['SETTINGS']['DISCOUNT'])) ? $botSettings['SETTINGS']['DISCOUNT'] : array();

$messageDiscount = '[b]Размер скидки в процентах в зависимости от суммы заказа:[/b]';
$messageDiscount .= '[br]от [b]'. key($arrDiscount) .'[/b] ₽ — ';
$messageDiscount .= '[b]'. current($arrDiscount) .'%[/b]';
$messageDiscount .= '[br][put=/edit_discount_summ '. key($arrDiscount) .']Изменить пороговую сумму[/put]';
$messageDiscount .= '[br][put=/edit_discount_sale '. current($arrDiscount) .']Изменить размер скидки[/put]';
$messageDiscount .= '[br][send=/discount delete]Удалить[/send]';
$resDiscount = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => $messageDiscount,
	'KEYBOARD' => $keyboard,
), $_REQUEST['auth']);
