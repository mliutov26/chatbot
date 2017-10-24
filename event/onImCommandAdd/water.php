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
$resWater = restCommand('entity.item.get', Array(
	'ENTITY' => CATALOG_CODE,
	'SORT' => array(
		'DATE_ACTIVE_FROM' => 'ASC',
		'ID' => 'ASC',
	),
), $_REQUEST['auth']);
//cnLog::Add('water resWater: '.print_r($resWater, true));

if (array_key_exists('error', $resWater) && !empty($resWater['error'])) {
	$resultWaterMess = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => 'Ой, что-то пошло не так. :( [br] Не удалось загрузить товары',
		'KEYBOARD' => $keyboard,
	), $_REQUEST['auth']);

} else {

	if(empty($resWater['result'])) {
		$mess = 'В каталоге воды еще нет товаров. [send=/add_water]Добавить товар[/send]';
	} else {						
		$mess = '[b]Каталог воды:[/b]';
		foreach ($resWater['result'] as $water) {
			$mess .= $water['PREVIEW_PICTURE'];
			$mess .= '[br][send=/edit_water '.$water['ID'].']Редактировать[/send] ';
			$mess .= '[send=/delete_water '.$water['ID'].']Удалить[/send][br][br]';
		}
	}

	$resultWaterMess = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $command['MESSAGE_ID'],
		'MESSAGE' => $mess,
		'KEYBOARD' => $keyboard,
	), $_REQUEST['auth']);
}
