<?php
// cnLog::Add('REQUEST: '.print_r($_REQUEST, true));
// cnLog::Add('botSettings: '.print_r($botSettings, true));

$keyboard = Array(
    Array('TYPE' => 'NEWLINE'),
    Array(
        'TEXT' => 'Доступные команды',
        'BG_COLOR' => '#e8e9eb',
        'TEXT_COLOR' => '#333',
        'DISPLAY' => 'LINE',
        'COMMAND' => 'settings',
    ),
);

$newBotSettings = array(
	'SETTINGS' => array(
        'ADMINS' => $botSettings['SETTINGS']['ADMINS'],
		'ADMIN_CRM' => $botSettings['SETTINGS']['ADMIN_CRM'],
		'CURR_ADMIN_ID' => $botSettings['SETTINGS']['CURR_ADMIN_ID'],
		'CURR_ADMIN_NAME' => $botSettings['SETTINGS']['CURR_ADMIN_NAME']
	)
);

resetSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth']);
resetSettings(WBOT_CODE, $_REQUEST['PARAMS']['DIALOG_ID'], $_REQUEST['auth']);
saveSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth'], $newBotSettings['SETTINGS']);
$appSettings = loadSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth'], true);
$appDialogSettings = loadSettings(WBOT_CODE, $_REQUEST['PARAMS']['DIALOG_ID'], $_REQUEST['auth'], true);

// cnLog::Add('appSettings: '.print_r($appSettings, true));
// cnLog::Add('appDialogSettings: '.print_r($appDialogSettings, true));


$result = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => 'Настройки сброшены',
	'KEYBOARD' => $keyboard,
), $_REQUEST['auth']);

//cnLog::Add('botSettings admins: '.print_r($botSettings, true));
