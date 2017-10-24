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
		'COMMAND_PARAMS' => 'water accessories discount delivery refund fine admins hello settings',
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
	$dialogSettings['SETTINGS']['STATE'] = 'add_fine';
	$dialogSettings['SETTINGS']['PARAM'] = 'fine_mess';
	saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

	$resHello = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Введите информацию о стоимости бутылки в рублях в случае невозврата:',
	), $_REQUEST['auth']);
	exit;
}
if($command['COMMAND_PARAMS'] == 'edit') {
	$dialogSettings['SETTINGS']['STATE'] = 'edit_fine';
	$dialogSettings['SETTINGS']['PARAM'] = 'fine_mess';
	saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

	$resHello = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => '[b]Текущая стоимость бутылки в случае невозврата:[/b] '.$botSettings['SETTINGS']['FINE_MESS'].' ₽[br] Введите новую стоимость в рублях',
	), $_REQUEST['auth']);
	exit;
}

if($command['COMMAND_PARAMS'] == 'delete') {
	$botSettings['SETTINGS']['FINE_MESS'] = '';
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
		'MESSAGE' => 'Информация о стоимости бутылки в случае невозврата удалена. [send=/fine add]Добавить информацию[/send]',
		'KEYBOARD' => $keyboard,
	), $_REQUEST['auth']);
	exit;
}

if(!array_key_exists('FINE_MESS', $botSettings['SETTINGS']) || empty($botSettings['SETTINGS']['FINE_MESS'])) {
	$resDelivery = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Стоимость бутылки в случае невозврата еще не указана. [send=/fine add]Добавить информацию[/send]',
		'KEYBOARD' => $keyboard,
	), $_REQUEST['auth']);
	exit;
}

$messageRefund = '[b]Стоимость бутылки в случае невозврата:[/b] '. $botSettings['SETTINGS']['FINE_MESS'].' ₽';
$messageRefund .= '[br][send=/fine edit]Редактировать[/send][br][send=/fine delete]Удалить[/send]';
$resRefund = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => $messageRefund,
	'KEYBOARD' => $keyboard,
), $_REQUEST['auth']);
