<?php

$managerId = intval($command['COMMAND_PARAMS']);

if($managerId) {
	$arManager = getUsers($_REQUEST['auth'], array('ID' => $managerId));
	$managerName = current($arManager);

	unset($botSettings['SETTINGS']['MANAGERS'][$managerId]);
	saveSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth'], $botSettings['SETTINGS']);

    $mess = '[b]'.$managerName.'[/b] больше не ответственный за обработку заказов';
	//$mess = '[b]'.$managerName.'[/b] больше не ответственный за обработку заказов[br][send=/managers]Посмотреть всех ответственных[/send]';

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
			'COMMAND_PARAMS' => 'managers set_managers unset_managers',
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
	$mess = 'Ответственный менеджер не найден, проверьте данные или попробуйте еще раз [br][send=/unset_managers]Удалить менеджера[/send]';	
	$keyboard = array();				
}

$result = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => $mess,
	'KEYBOARD' => $keyboard,
), $_REQUEST['auth']);
