<?php

$mess = '[send=/admins]Список администраторов[/send]';
$mess .= '[br][send=/managers]Посмотреть ответственного за обработку заказов[/send]';

$mess .= '[br]';

$mess .= '[br][send=/water]Каталог воды[/send]';
$mess .= '[br][send=/add_water]Добавить товар[/send]';
$mess .= '[br][send=/accessories]Каталог аксессуаров[/send]';
$mess .= '[br][send=/add_accessories]Добавить  аксессуар[/send]';

$mess .= '[br]';

$mess .= '[br][send=/hello]Посмотреть текст приветствия[/send]';
$mess .= '[br][send=/delivery]Посмотреть информацию о доставке[/send]';
$mess .= '[br][send=/thanks]Посмотреть текст благодарности за оформление заказа[/send]';

$mess .= '[br]';

$mess .= '[br][send=/discount]Посмотреть размер скидки в процентах в зависимости от суммы заказа[/send]';
$mess .= '[br][send=/refund]Посмотреть размер скидки в рублях за каждую возвращённую бутылку[/send]';
$mess .= '[br][send=/fine]Посмотреть стоимость бутылки в случае невозврата[/send]';

$mess .= '[br]';

$mess .= '[br][send=/reset_settings]Сбросить все настройки[/send]';

$resRefund = restCommand('imbot.command.answer', Array(
	'COMMAND_ID' => $command['COMMAND_ID'],
	'MESSAGE_ID' => $command['MESSAGE_ID'],
	'MESSAGE' => '[b]Доступные команды:[/b][br]'.$mess,
), $_REQUEST['auth']);
