<?php

$chatType = explode('|', $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID']);

if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {

    $messCommand = '[send=add_address]Указать адрес доставки[/send]';
    $messCommand .= '[br][send=new_order]Добавить ещё воды и аксессуаров[/send]';
    $messCommand .= '[br][send=communication]Связаться с оператором[/send]';

} else {

    $messCommand = '2: Указать адрес доставки[br]';
    $messCommand .= '1: Добавить ещё воды и аксессуаров[br]';
    $messCommand .= '0: Связаться с оператором';

}

if ($chatType['0'] != 'livechat' && $chatType['0'] != 'network') {
    $deliveryMessage = 'Условия доставки:[br]';
} else {
    $deliveryMessage = '[b]Условия доставки:[/b][br]';
}
$deliveryMessage .= 'Информация об условиях доставки отсутствует';

if ($botSettings['SETTINGS']['DELIVERY_MESS']) {
	$deliveryMessage = $botSettings['SETTINGS']['DELIVERY_MESS'];
}

if ($chatType['0'] != 'livechat' && $chatType['0'] != 'network') {
    $dialogSettings['SETTINGS']['PARAM_COMMAND'] = '';
    saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);
}

$result = restCommand('imbot.message.add', Array(
	"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
	"MESSAGE" => $deliveryMessage.'[br][br]'.$messCommand,
), $_REQUEST["auth"]);
