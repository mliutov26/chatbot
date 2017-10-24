<?php

if(!is_array($dialogSettings['SETTINGS']['STATE'])) {
	$dialogSettings['SETTINGS']['STATE'] = array();
}
if(!is_array($dialogSettings['SETTINGS']['PARAM'])) {
	$dialogSettings['SETTINGS']['PARAM'] = array();
}
$dialogSettings['SETTINGS']['STATE'] = 'add_accessories';
$dialogSettings['SETTINGS']['PARAM'] = 'name';

saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

//cnLog::Add('add_water dialogSettings: '.print_r($dialogSettings, true));

$result = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => 'Введите название аксессуара',
), $_REQUEST['auth']);
