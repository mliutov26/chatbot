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
    'COMMAND_PARAMS' => 'admins set_admins unset_admins water add_water',
),
Array(
    'TEXT' => 'Сбросить все настройки',
    'BG_COLOR' => '#aeb1b7',
    'TEXT_COLOR' => '#f7f6f6',
    'DISPLAY' => 'LINE',
    'COMMAND' => 'reset_settings',
),*/
);
$adminId = intval($command['COMMAND_PARAMS']);
$mess = 'Добавлен новый администратор: ';

if($adminId) {
	$arUser = getUsers($_REQUEST['auth'], array('ID' => $adminId));
	//cnLog::Add('add_admin: '.print_r($arUser, true));
	if(empty($arUser)) {
		$mess = 'Админ не найден';
		$keyboard = array();
	} else {
		$userName = current($arUser);
		// cnLog::Add('$arUser: '.print_r($arUser, true));
		// cnLog::Add('$userName: '.print_r($userName, true));
	}
	// функция добавления и изменения настроек
	//$loadedSettings = loadSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth']);
	if(!is_array($botSettings['SETTINGS']['ADMINS'])) {
		$botSettings['SETTINGS']['ADMINS'] = array();
	}

	$botSettings['SETTINGS']['ADMINS'][$adminId] = $userName;
	$mess .= '[b]'.$userName.'[/b][br][send=/admins]Посмотреть всех администраторов[/send]';

	//cnLog::Add('$loadedSettings[SETTINGS]: '.print_r($loadedSettings['SETTINGS'], true));
	saveSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth'], $botSettings['SETTINGS']);
} else {
	$mess = 'Ошибка добавления админа, попробуйте еще раз :)';
	$keyboard = array();
}

$result = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => $mess,
	'KEYBOARD' => $keyboard
), $_REQUEST['auth']);

//cnLog::Add('botSettings add_admins: '.print_r($botSettings, true));
