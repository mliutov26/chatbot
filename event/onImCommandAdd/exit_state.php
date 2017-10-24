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
		'COMMAND_PARAMS' => 'admins water accessories delivery discount refund fine',
	),
	Array(
		'TEXT' => 'Сбросить все настройки',
		'BG_COLOR' => '#aeb1b7',
		'TEXT_COLOR' => '#f7f6f6',
		'DISPLAY' => 'LINE',
		'COMMAND' => 'reset_settings',
	),*/
);

$dialogSettings['SETTINGS'] = array(
	'STATE' => '',
	'PARAM' => '',
	'ID' => '',
);

if(!empty($dialogSettings['SETTINGS'])) {
	$state = $dialogSettings['SETTINGS']['STATE'];
	$param = $dialogSettings['SETTINGS']['PARAM'];
	$idProduct = $dialogSettings['SETTINGS']['ID'];

	if($state == 'add_water') {
		$resDelete = restCommand('entity.item.delete', Array(
			'ENTITY' => CATALOG_CODE,
			'ID' => $idProduct,
		), $_REQUEST['auth']);

		//cnLog::Add('exit_state entity.item.delete: '.print_r($resDelete, true));

		if(array_key_exists('error', $resDelete) && !empty($resDelete['error'])) {
			$resMess = restCommand('imbot.command.answer', Array(
				'COMMAND_ID' => $command['COMMAND_ID'],
				'MESSAGE_ID' => $command['MESSAGE_ID'],
				'MESSAGE' => 'Ой, что-то пошло не так :( [br]Не удалось выйти из режима',
			), $_REQUEST['auth']);
			exit;
		}

		saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);
		$resMess = restCommand('imbot.command.answer', Array(
			'COMMAND_ID' => $command['COMMAND_ID'],
			'MESSAGE_ID' => $command['MESSAGE_ID'],
			'MESSAGE' => 'Вы вышли из режима добавления товара',
			'KEYBOARD' => $keyboard,
		), $_REQUEST['auth']);
		//cnLog::Add('dialogSettings add_water exit: '.print_r($dialogSettings, true));
		exit;
	}
}

saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);
$result = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => 'Редактирование завершено. Полный список настроек можно посмотреть в «Доступных командах».',
	'KEYBOARD' => $keyboard,
), $_REQUEST['auth']);