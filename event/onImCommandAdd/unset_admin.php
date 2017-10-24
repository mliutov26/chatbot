<?php

$adminId = intval($command['COMMAND_PARAMS']);
//cnLog::Add('unset_admin arAdmins: '.print_r($adminId, true));

if($adminId) {
	$arUser = getUsers($_REQUEST['auth'], array('ID' => $adminId));
	$userName = current($arUser);
	//$loadedSettings = loadSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth']);

	unset($botSettings['SETTINGS']['ADMINS'][$adminId]);
	saveSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth'], $botSettings['SETTINGS']);

	$mess = '[b]'.$userName.'[/b] больше не администратор[br][send=/admins]Посмотреть всех администраторов[/send]';

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
			'COMMAND_PARAMS' => 'admins set_admins unset_admins',
		),
		Array(
			'TEXT' => 'Сбросить все настройки',
			'BG_COLOR' => '#aeb1b7',
			'TEXT_COLOR' => '#f7f6f6',
			'DISPLAY' => 'LINE',
			'COMMAND' => 'reset_settings',
		),*/
	);

} else {
	$mess = 'Администратор не найден, проверьте данные или попробуйте еще раз [br][send=/unset_admins]Удалить администратора[/send]';	
	$keyboard = array();				
}

$result = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => $mess,
	'KEYBOARD' => $keyboard,
), $_REQUEST['auth']);

//cnLog::Add('botSettings unset_admin: '.print_r($botSettings, true));
