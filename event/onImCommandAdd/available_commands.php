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
    Array('TYPE' => 'NEWLINE'),*/
);

//cnLog::Add('COMMAND_PARAMS: '.print_r($command['COMMAND_PARAMS'], true));
if($command['COMMAND_PARAMS']) {
	$arCommandsName = explode(' ', $command['COMMAND_PARAMS']);
	//cnLog::Add('arCommandsName: '.print_r($arCommandsName, true));
} else {
	$arCommandsName = array('help');
	//cnLog::Add('arCommandsName: '.print_r($arCommandsName, true));
}

$buttons = getCommandBtn($arCommandsName, $commands);
//cnLog::Add('available_commands: '.print_r($buttons, true));
$mess = '[b]Доступные команды:[/b] '.$buttons;

$result = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => $mess,
    'KEYBOARD' => $keyboard,
), $_REQUEST['auth']);
