<?php

//$arSettings = loadSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth']);
$arAdmins = (empty($botSettings['SETTINGS']['ADMINS'])) ? array() : $botSettings['SETTINGS']['ADMINS'];

if (empty($arAdmins)) {
	$mess = 'Удминистраторы еще не установлены. [send=/set_admins]Установить администраторов[/send]';
} else {
	$mess = 'Для удаления администратора кликните по его имени в списке ниже (можно выбрать нескольких):';
	foreach ($arAdmins as $idAdmin => $admin) {
		$mess .= '[br][send=/unset_admin '.$idAdmin.']'.$admin.'[/send]';
	}
}

$result = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => $mess,
), $_REQUEST['auth']);
//cnLog::Add('botSettings unset_admins: '.print_r($botSettings, true));
