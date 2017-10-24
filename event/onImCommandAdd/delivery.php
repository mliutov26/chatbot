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

if($command['COMMAND_PARAMS'] == 'delete') {
	$botSettings['SETTINGS']['DELIVERY_MESS'] = array();
	saveSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth'], $botSettings['SETTINGS']);

	$dialogSettings['SETTINGS'] = array(
			'STATE' => '',
			'PARAM' => '',
			'ID' => '',
		);
	saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

	$resDelivery = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Информация о доставке удалена',
		'KEYBOARD' => $keyboard,
	), $_REQUEST['auth']);
	exit;
}

if(!array_key_exists('DELIVERY_MESS', $botSettings['SETTINGS']) || empty($botSettings['SETTINGS']['DELIVERY_MESS'])) {
	$resDelivery = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Информации о доставке пока нет. [send=/add_delivery]Добавить информацию[/send]',
	), $_REQUEST['auth']);
	exit;
}

$messageDelivery = '[b]Информация о доставке:[/b][br]'. $botSettings['SETTINGS']['DELIVERY_MESS'];
$messageDelivery .= '[br][send=/edit_delivery]Редактировать[/send][br][send=/delivery delete]Удалить[/send]';
$resDelivery = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => $messageDelivery,
	'KEYBOARD' => $keyboard,
), $_REQUEST['auth']);
