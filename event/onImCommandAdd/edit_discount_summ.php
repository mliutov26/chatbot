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
$keySale = $command['COMMAND_PARAMS'];
$valueSale = current($botSettings['SETTINGS']['DISCOUNT']);
unset($botSettings['SETTINGS']['DISCOUNT']);
$botSettings['SETTINGS']['DISCOUNT'] = array($keySale => $valueSale);

cnLog::Add('discount $keySale: '.print_r($keySale, true));
cnLog::Add('discount $valueSale: '.print_r($valueSale, true));

saveSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth'], $botSettings['SETTINGS']);

$resDiscount = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => 'Минимальная сумма заказа для получения скидки обновлена. [send=/discount]Посмотреть[/send]',
	'KEYBOARD' => $keyboard,
), $_REQUEST['auth']);
