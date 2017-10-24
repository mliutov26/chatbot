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
		'COMMAND_PARAMS' => 'water accessories discount delivery refund fine admins hello settings',
	),
	Array(
		'TEXT' => 'Сбросить все настройки',
		'BG_COLOR' => '#aeb1b7',
		'TEXT_COLOR' => '#f7f6f6',
		'DISPLAY' => 'LINE',
		'COMMAND' => 'reset_settings',
	),*/
);

if($command['COMMAND_PARAMS'] == 'add') {
	$dialogSettings['SETTINGS']['STATE'] = 'add_refund';
	$dialogSettings['SETTINGS']['PARAM'] = 'refund_mess';
	saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

	$resHello = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Введите цену в рублях за 1 возвращенную бутылку:',
	), $_REQUEST['auth']);
	exit;
}
if($command['COMMAND_PARAMS'] == 'edit') {
	$dialogSettings['SETTINGS']['STATE'] = 'edit_refund';
	$dialogSettings['SETTINGS']['PARAM'] = 'refund_mess';
	saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

	$resHello = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Отредактируйте цену в рублях за 1 возвращенную бутылку:[br]'.$botSettings['SETTINGS']['REFUND_MESS'],
	), $_REQUEST['auth']);
	exit;
}

if($command['COMMAND_PARAMS'] == 'delete') {
	$botSettings['SETTINGS']['REFUND_MESS'] = '';
	saveSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth'], $botSettings['SETTINGS']);

	$dialogSettings['SETTINGS'] = array(
		'STATE' => '',
		'PARAM' => '',
		'ID' => ''
	);
	saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

	$resHello = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Информация о скидках на возвращенные бутылки удалена',
		'KEYBOARD' => $keyboard,
	), $_REQUEST['auth']);
	exit;
}

if(!array_key_exists('REFUND_MESS', $botSettings['SETTINGS']) || empty($botSettings['SETTINGS']['REFUND_MESS'])) {
	$resDelivery = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Размер скидки в рублях за каждую возвращённую бутылку еще не указан. [send=/refund add]Добавить информацию[/send]',
	), $_REQUEST['auth']);
	exit;
}

$messageRefund = '[b]Информация о скидках на возвращенные бутылки:[/b] 1 бутылка - '.$botSettings['SETTINGS']['REFUND_MESS'].' ₽';
$messageRefund .= '[br][send=/refund edit]Редактировать[/send][br][send=/refund delete]Удалить[/send]';
$resRefund = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => $messageRefund,
	'KEYBOARD' => $keyboard,
), $_REQUEST['auth']);
