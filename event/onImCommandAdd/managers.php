<?php

$arManagers = (empty($botSettings['SETTINGS']['MANAGERS'])) ? array() : $botSettings['SETTINGS']['MANAGERS'];

if (empty($arManagers)) {
	$mess = 'Менеджер ответственный за обработку заказов еще не установлен. [send=/set_managers]Установить ответственного[/send]';
	$keyboard = array();
} else {
	$mess = '[b]Ответственный за обработку заказов:[/b]';
	foreach ($arManagers as $manager) {
		$mess .= '[br]'.$manager;
	}
    $mess .= '[br][send=/set_managers]Изменить менеджера[/send][br][send=/unset_managers]Удалить менеджера[/send]';
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
			'COMMAND_PARAMS' => 'unset_managers set_managers accessories delivery discount refund fine admins',
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
	'MESSAGE' => $mess,
	'KEYBOARD' => $keyboard,
), $_REQUEST['auth']);
