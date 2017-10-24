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
		'COMMAND_PARAMS' => 'add_water water accessories delivery discount refund fine admins settings',
	),
	Array(
		'TEXT' => 'Сбросить все настройки',
		'BG_COLOR' => '#aeb1b7',
		'TEXT_COLOR' => '#f7f6f6',
		'DISPLAY' => 'LINE',
		'COMMAND' => 'reset_settings',
	),*/
);

if($event == 'ONIMCOMMANDADD') {
	if($command['COMMAND'] !== 'exit_state' && $command['COMMAND'] !== 'reset_settings') {
		$result = restCommand('imbot.command.answer', Array(
			'COMMAND_ID' => $command['COMMAND_ID'],
			'MESSAGE_ID' => $messageId,
			'MESSAGE' => 'Бот находится в режиме редактирования приветственного сообщения. Введите сообщение или [send=/exit_state]выйдите из режима[/send]',
			'KEYBOARD' => $keyboard,
		), $arAuth);
		exit;
	}
} else {
	$botSettings['SETTINGS']['HELLO_MESS'] = $message;
	saveSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth'], $botSettings['SETTINGS']);
	$dialogSettings['SETTINGS'] = array(
		'STATE' => '',
		'PARAM' => '',
		'ID' => ''
	);
	saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);
	$resultHello = restCommand('imbot.message.add', Array(
		'DIALOG_ID' => $dialogId,
		'MESSAGE' => 'Приветственное сообщение обновлено. [send=/hello]Посмотреть[/send]',
		'KEYBOARD' => $keyboard,
	), $arAuth);
	exit;
}
