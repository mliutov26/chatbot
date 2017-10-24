<?php

if($command['COMMAND_PARAMS'] == 'add') {
	$botSettings['SETTINGS']['THANKS_MESS'] = array();
	saveSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth'], $botSettings['SETTINGS']);

	$dialogSettings['SETTINGS']['STATE'] = 'add_thanks';
	$dialogSettings['SETTINGS']['PARAM'] = 'thanks_mess';
	saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

	$resThanks = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Введите текст благодарности за оформление заказа',
	), $_REQUEST['auth']);
	exit;
}

if($command['COMMAND_PARAMS'] == 'edit') {
	$dialogSettings['SETTINGS']['STATE'] = 'edit_thanks';
	$dialogSettings['SETTINGS']['PARAM'] = 'thanks_mess';
	saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

    $resThanks = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Отредактируйте текст благодарности за оформление заказа:[br]'.$botSettings['SETTINGS']['THANKS_MESS'],
	), $_REQUEST['auth']);
	exit;
}

if(!array_key_exists('THANKS_MESS', $botSettings['SETTINGS']) || empty($botSettings['SETTINGS']['THANKS_MESS'])) {
	$resDelivery = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Текста благодарности за оформление заказа пока нет. [send=/thanks add]Добавить информацию[/send]',
	), $_REQUEST['auth']);
	exit;
}

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
		'COMMAND_PARAMS' => 'water accessories discount delivery refund fine admins hello',
	),
	Array(
		'TEXT' => 'Сбросить все настройки',
		'BG_COLOR' => '#aeb1b7',
		'TEXT_COLOR' => '#f7f6f6',
		'DISPLAY' => 'LINE',
		'COMMAND' => 'reset_settings',
	),*/
);

$messageThanks = '[b]Текст благодарности за оформление заказа:[/b][br]'.$botSettings['SETTINGS']['THANKS_MESS'];
$messageThanks .= '[br][send=/thanks edit]Редактировать[/send]';
$resThanks = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => $messageThanks,
	'KEYBOARD' => $keyboard,
), $_REQUEST['auth']);

