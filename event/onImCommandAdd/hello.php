<?php

if($command['COMMAND_PARAMS'] == 'add') {
	$botSettings['SETTINGS']['HELLO_MESS'] = array();
	saveSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth'], $botSettings['SETTINGS']);

	$dialogSettings['SETTINGS']['STATE'] = 'add_hello';
	$dialogSettings['SETTINGS']['PARAM'] = 'hello_mess';
	saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

	$resHello = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Введите приветственное сообщение',
	), $_REQUEST['auth']);
	exit;
}

if($command['COMMAND_PARAMS'] == 'edit') {
	$dialogSettings['SETTINGS']['STATE'] = 'edit_hello';
	$dialogSettings['SETTINGS']['PARAM'] = 'hello_mess';
	saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

	$resHello = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Отредактируйте приветственное сообщение:[br]'.$botSettings['SETTINGS']['HELLO_MESS'],
	), $_REQUEST['auth']);
	exit;
}

if(!array_key_exists('HELLO_MESS', $botSettings['SETTINGS']) || empty($botSettings['SETTINGS']['HELLO_MESS'])) {
	$resDelivery = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Приветственного сообщения пока нет. [send=/hello add]Добавить информацию[/send]',
	), $_REQUEST['auth']);
	exit;
}

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

$messageHello = '[b]Приветственное сообщение:[/b][br]'.$botSettings['SETTINGS']['HELLO_MESS'];
$messageHello .= '[br][send=/hello edit]Редактировать[/send]';
$resHello = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => $messageHello,
	'KEYBOARD' => $keyboard,
), $_REQUEST['auth']);

