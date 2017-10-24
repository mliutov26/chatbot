<?php

//$arSettings = loadSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth']);
$arAdmins = (empty($botSettings['SETTINGS']['ADMINS'])) ? array() : $botSettings['SETTINGS']['ADMINS'];

// cnLog::Add('settings: '.print_r($arSettings, true));
// cnLog::Add('arAdmins: '.print_r($arAdmins, true));

if (empty($arAdmins)) {
	$mess = 'Удминистраторы еще не установлены.';
	$keyboard = array();
} else {
	$mess = '[b]Список администраторов:[/b]';
	foreach ($arAdmins as $admin) {
		$mess .= '[br]'.$admin;
	}
	//$mess .= 'Доступные команды:[br]'.$buttons;
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
			'COMMAND_PARAMS' => 'unset_admins set_admins add_water',
		),
		Array(
			'TEXT' => 'Сбросить все настройки',
			'BG_COLOR' => '#aeb1b7',
			'TEXT_COLOR' => '#f7f6f6',
			'DISPLAY' => 'LINE',
			'COMMAND' => 'reset_settings',
		),*/
	);

	//$buttons = getCommandBtn(array('unset_admins', 'set_admins'), $commands);
}

$result = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => $mess.'[br][send=/set_admins]Добавить администратора[/send][br][send=/unset_admins]Удалить администратора[/send]',
	'KEYBOARD' => $keyboard,
), $_REQUEST['auth']);
//cnLog::Add('botSettings admins: '.print_r($botSettings, true));
