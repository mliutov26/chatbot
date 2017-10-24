<?php

$idDeletedWater = $command['COMMAND_PARAMS'];
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
		'COMMAND_PARAMS' => 'add_water accessories delivery discount refund fine admins',
	),
	Array(
		'TEXT' => 'Сбросить все настройки',
		'BG_COLOR' => '#aeb1b7',
		'TEXT_COLOR' => '#f7f6f6',
		'DISPLAY' => 'LINE',
		'COMMAND' => 'reset_settings',
	),*/
);
$resDeleteWater = restCommand('entity.item.delete', Array(
	'ENTITY' => CATALOG_CODE,
	'ID' => $idDeletedWater,
), $_REQUEST['auth']);
//cnLog::Add('delete_water resDeleteWater: '.print_r($resDeleteWater, true));

if (array_key_exists('error', $resDeleteWater) && !empty($resDeleteWater['error'])) {
	$deleteWaterMess = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Ой, что-то пошло не так. :( [br] Не удалось удалить товар',
		'KEYBOARD' => $keyboard,
	), $_REQUEST['auth']);

} else {
	$deleteWaterMess = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Товар удален из каталога. [send=/water]Посмотреть все товары[/send]',
		'KEYBOARD' => $keyboard,
	), $_REQUEST['auth']);				
}
