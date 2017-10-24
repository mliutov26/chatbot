<?php

$resBasket = restCommand('entity.item.get', Array(
	'ENTITY' => BASKET_CODE,
	'FILTER' => array(
		'PROPERTY_BASKET_USER_ID' => $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID'],
		'PROPERTY_BASKET_TYPE' => '2',
	),
	'SORT' => array(
		'DATE_ACTIVE_FROM' => 'ASC',
		'ID' => 'ASC',
	),
), $_REQUEST['auth']);

if (array_key_exists('error', $resBasket) && !empty($resBasket['error'])) {
	
	$result = restCommand('imbot.message.add', Array(
		"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
		"MESSAGE" => 'Ой, что-то пошло не так. :( [br] Не удалось загрузить товары',
	), $_REQUEST["auth"]);

} else {
	
	$resAccessories = restCommand('entity.item.get', Array(
		'ENTITY' => CAT_DETAILS,
		'SORT' => array(
			'DATE_ACTIVE_FROM' => 'ASC',
			'ID' => 'ASC',
		),
	), $_REQUEST['auth']);

    if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {

        if (!empty($resAccessories['result'])) {
        	
            $messCommand = '[send=new_order_accessories]У меня нет бутылей на возврат[/send]';
            
        } else {
        	
			$resOrder = restCommand('entity.item.get', Array(
				'ENTITY' => ORDER_CODE,
				'FILTER' => array(
					'PROPERTY_ORDER_USER_ID' => $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID'],
					'PROPERTY_ORDER_STATUS' => 'Y',
				),
				'SORT' => array(
					'DATE_ACTIVE_FROM' => 'ASC',
					'ID' => 'ASC',
				),
			), $_REQUEST['auth']);
			
			if ($resOrder['result']) {
				$messCommand = '[send=add_address]Указать адрес доставки[/send]';
			} else {
				$messCommand = '[put=add_address]Указать адрес доставки[/put]';				
			}

			if ($botSettings['SETTINGS']['DELIVERY_MESS']) {
				$messAdditional = '[br]'.$botSettings['SETTINGS']['DELIVERY_MESS'].'[br]';
			}
				
        }

        $messCommand .= '[br][send=communication]Связаться с оператором[/send]';

        $mess = '[b]Выберите количество пустых бутылок на возврат:[/b][br]';
        if ($botSettings['SETTINGS']['FINE_MESS']) {
            $mess .= 'Внимание! За невозврат наших бутылок может взиматься штраф в размере '.$botSettings['SETTINGS']['FINE_MESS'].' ₽ за 1 бутылку.[br]';
        }
        if ($resBasket['result']) {
            $mess .= '[send=delete_basket_refund](X)[/send] ';
        }
        $mess .= '[send=add_basket_refund 0]0[/send] ';
        $mess .= '[send=add_basket_refund 1]1[/send] ';
        $mess .= '[send=add_basket_refund 2]2[/send] ';
        $mess .= '[send=add_basket_refund 3]3[/send] ';
        $mess .= '[send=add_basket_refund 4]4[/send] ';
        $mess .= '[send=add_basket_refund 5]5[/send] ';
        $mess .= '[send=add_basket_refund 6]6[/send] ';
        $mess .= '[br]';

    } else {

        $mess = 'Выберите количество пустых бутылок на возврат:[br]';
        if ($botSettings['SETTINGS']['FINE_MESS']) {
            $mess .= 'Внимание! За невозврат наших бутылок может взиматься штраф в размере '.$botSettings['SETTINGS']['FINE_MESS'].' ₽ за 1 бутылку.[br]';
        }
        $mess .= '[br]11: Возврат 1 бутылки';
        $mess .= '[br]12: Возврат 2 бутылок';
        $mess .= '[br]13: Возврат 3 бутылок';
        $mess .= '[br]14: Возврат 4 бутылок';
        $mess .= '[br]15: Возврат 5 бутылок';
        $mess .= '[br]16: Возврат 6 бутылок';

        if (!empty($resAccessories['result'])) {
        	
			$messCommand = '[br]1: У меня нет бутылей на возврат';
			
        } else {
        
			$messCommand = '[br]1: Указать адрес доставки';
			
			if ($botSettings['SETTINGS']['DELIVERY_MESS']) {
				$messAdditional = '[br][br]'.$botSettings['SETTINGS']['DELIVERY_MESS'];
			}
        }

        $messCommand .= '[br][send=communication]Связаться с оператором[/send]';
    }
    
	if ($messAdditional) {
		$mess .= $messAdditional;
	}

	$result = restCommand('imbot.message.add', Array(
		"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
		"MESSAGE" => $mess.'[br]'.$messCommand,
	), $_REQUEST["auth"]);
	
}
