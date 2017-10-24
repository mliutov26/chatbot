<?php

$dialogSettings['SETTINGS']['PARAM'] = 'image';
saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);
$editWaterMess = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => 'Введите ссылку на изображение (окончание ссылки должно быть .png, .jpg, .gif)',
), $_REQUEST['auth']);
