<?php

//$botSettings['SETTINGS']['CURR_ADMIN_ID'] = $newAdminId;

$botSettings['SETTINGS']['CURR_ADMIN_ID'] = $_REQUEST['data']['USER']['ID'];
$botSettings['SETTINGS']['CURR_ADMIN_NAME'] = $_REQUEST['data']['USER']['NAME'];
saveSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth'], $botSettings['SETTINGS']);
$result = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => 'Смена админа, редактирующего настройки прошла успешно. [send=/settings]Команды[/send]',
), $_REQUEST['auth']);
