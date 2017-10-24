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
		'COMMAND_PARAMS' => 'add_water water accessories delivery discount refund fine admins',
	),
	Array(
		'TEXT' => 'Сбросить все настройки',
		'BG_COLOR' => '#aeb1b7',
		'TEXT_COLOR' => '#f7f6f6',
		'DISPLAY' => 'LINE',
		'COMMAND' => 'reset_settings',
	),*/
);
$resAccessories = restCommand('entity.item.get', Array(
	'ENTITY' => CAT_DETAILS,
	'SORT' => array(
		'DATE_ACTIVE_FROM' => 'ASC',
		'ID' => 'ASC',
	),
), $_REQUEST['auth']);


if(array_key_exists('error', $resDelete) && !empty($resDelete['error'])) {
	$resMess = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Ой, что-то пошло не так :(',
	), $_REQUEST['auth']);
	exit;
}

if(empty($resAccessories['result'])) {
	$mess = 'В каталоге аксессуаров еще нет товаров. [send=/add_accessories]Добавить товар[/send]';
} else {						
	$mess = '[b]Каталог аксессуаров:[/b]';
	foreach ($resAccessories['result'] as $accessory) {
		$mess .= $accessory['PREVIEW_PICTURE'];
		$mess .= '[br][send=/edit_accessories '.$accessory['ID'].']Редактировать[/send] ';
		$mess .= '[send=/unset_accessories '.$accessory['ID'].']Удалить[/send][br][br]';
	}
}

$resMess = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => $mess,
	'KEYBOARD' => $keyboard,
), $_REQUEST['auth']);
