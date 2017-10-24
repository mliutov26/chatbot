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
		'COMMAND_PARAMS' => 'add_water water accessories delivery discount refund fine admins hello settings',
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
			'MESSAGE' => 'Бот находится в режиме редактирования информации о стоимости бутылки в случае невозврата.  Введите стоимость бутылки в рублях или [send=/exit_state]выйдите из режима[/send]',
		), $arAuth);
		exit;
	}
} else {
	validateNumber($message, $dialogId, $arAuth);
	$botSettings['SETTINGS']['FINE_MESS'] = $message;
	saveSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth'], $botSettings['SETTINGS']);
	cnLog::Add('edit_fine $botSettings: '.print_r($botSettings, true));
	$dialogSettings['SETTINGS'] = array(
		'STATE' => '',
		'PARAM' => '',
		'ID' => '',
		'DISCOUNT_SUMM' => ''
	);
	saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);
	cnLog::Add('edit_fine $dialogSettings: '.print_r($dialogSettings, true));
	$resultRefund = restCommand('imbot.message.add', Array(
		'DIALOG_ID' => $dialogId,
		'MESSAGE' => 'Сообщение отредактировано. [send=/fine]Посмотреть[/send]',
		'KEYBOARD' => $keyboard,
	), $arAuth);
	exit;
}
