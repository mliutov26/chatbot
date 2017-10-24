<?php

$result = restCommand('imbot.message.update', Array(
	// 'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => 'Добро пожаловать в чат. :) [br] До начала работы необходимо настроить бота:[br] установить администраторов и добавить товары в каталог',
	'KEYBOARD' => Array(
		Array(
			'TEXT' => 'Установить администраторов',
			'BG_COLOR' => '#abb2b9',
			'TEXT_COLOR' => '#fff',
			'DISPLAY' => 'LINE',
			'COMMAND' => 'set_admins',
			'DISABLED' => 'Y'
		),
		/*Array(
			'TEXT' => 'Сбросить все настройки',
			'BG_COLOR' => '#aeb1b7',
			'TEXT_COLOR' => '#f7f6f6',
			'DISPLAY' => 'LINE',
			'COMMAND' => 'reset_settings',
		),*/
	)
), $_REQUEST['auth']);


//функция возвращает массив пользователей
$mess = 'Выберите администратора из списка ниже, кликнув по имени (можно выбрать нескольких):';
$arrUsers = getUsers($_REQUEST['auth']);
foreach ($arrUsers as $userId => $userName) {
	$mess .= '[send=/add_admin '.$userId.']'.$userName.'[/send][br]';
}

//cnLog::Add('ONIMCOMMANDADD: '.print_r($mess, true));

$result = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => $mess,
	// 'KEYBOARD' => $keyboard
), $_REQUEST['auth']);

//cnLog::Add('botSettings set_admins: '.print_r($botSettings, true));
