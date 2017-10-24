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
		'COMMAND_PARAMS' => 'managers set_managers unset_managers admins water accessories delivery fine discount refund',
	),
	Array(
		'TEXT' => 'Сбросить все настройки',
		'BG_COLOR' => '#aeb1b7',
		'TEXT_COLOR' => '#f7f6f6',
		'DISPLAY' => 'LINE',
		'COMMAND' => 'reset_settings',
	),*/
);
$managerId = intval($command['COMMAND_PARAMS']);
$mess = 'Добавлен новый ответственный менеджер: ';

if($managerId) {
	$arUser = getUsers($_REQUEST['auth'], array('ID' => $managerId));

	if(empty($arUser)) {
		$mess = 'Менеджер не найден';
		$keyboard = array();
	} else {
		$userName = current($arUser);
	}

    $botSettings['SETTINGS']['MANAGERS'] = array();
	$botSettings['SETTINGS']['MANAGERS'][$managerId] = $userName;

    $mess .= '[b]'.$userName.'[/b]';
	//$mess .= '[b]'.$userName.'[/b][br][send=/managers]Посмотреть всех ответственных[/send]';
	saveSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth'], $botSettings['SETTINGS']);

} else {
	$mess = 'Ошибка добавления менеджера, попробуйте еще раз :)';
	$keyboard = array();
}

$result = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => $mess,
	'KEYBOARD' => $keyboard
), $_REQUEST['auth']);

//cnLog::Add('botSettings add_admins: '.print_r($botSettings, true));
