<?php

//$arSettings = loadSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth']);
$arManagers = (empty($botSettings['SETTINGS']['MANAGERS'])) ? array() : $botSettings['SETTINGS']['MANAGERS'];

if (empty($arManagers)) {
	$mess = 'Удминистраторы еще не установлены. [send=/set_managers]Установить администраторов[/send]';
} else {
	$mess = 'Для удаления администратора кликните по его имени в списке ниже:';
	foreach ($arManagers as $idManager => $manager) {
		$mess .= '[br][send=/delete_manager '.$idManager.']'.$manager.'[/send]';
	}
}

$result = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => $mess,
), $_REQUEST['auth']);

//cnLog::Add('botSettings unset_admins: '.print_r($botSettings, true));
