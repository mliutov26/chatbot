<?php

$chatType = explode('|', $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID']);

if ($chatType['0'] != 'livechat' && $chatType['0'] != 'network') {
    $messageString = $dialogSettings['SETTINGS']['PARAM_COMMAND'];
}

$resOrderN = restCommand('entity.item.get', Array(
	'ENTITY' => ORDER_CODE,
	'FILTER' => array(
		'PROPERTY_ORDER_USER_ID' => $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID'],
		'PROPERTY_ORDER_STATUS' => 'N',
	),
	'SORT' => array(
		'DATE_ACTIVE_FROM' => 'ASC',
		'ID' => 'ASC',
	),
), $_REQUEST['auth']);

$thisPhone = $resOrderN['result']['0']['PROPERTY_VALUES']['ORDER_PHONE'];

if ($messageString && $messageString != 'new_phone') {
	
	$resOrder = $resOrderN;
	
	if(empty($resOrder['result'])) {
		$resOrderAdd = restCommand('entity.item.add', Array(
			'ENTITY' => ORDER_CODE,
			'NAME' => 'Заказ от '.date("Y-m-d H:i:s"),
			'PROPERTY_VALUES' => array(
				'ORDER_PHONE' => $messageString,
				'ORDER_STATUS' => 'N',
				'ORDER_USER_ID' => $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID'],
			),
		), $_REQUEST['auth']);	
	} else {
		foreach ($resOrder['result'] as $order) {
			$resOrderUpdate = restCommand('entity.item.update', Array(
				'ENTITY' => ORDER_CODE,
				'ID' => $order['ID'],
				'PROPERTY_VALUES' => array(
					'ORDER_PHONE' => $messageString,
					'ORDER_STATUS' => 'N',
				),
			), $_REQUEST['auth']);
		}
	}
	
	$resBasket = restCommand('entity.item.get', Array(
		'ENTITY' => BASKET_CODE,
		'FILTER' => array(
			'PROPERTY_BASKET_USER_ID' => $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID'],
		),
		'SORT' => array(
			'DATE_ACTIVE_FROM' => 'ASC',
			'ID' => 'ASC',
		),
	), $_REQUEST['auth']);
	
	
	if((empty($resBasket['result'])) || (($resBasket['total'] == 1) && ($resBasket['result']['0']['PROPERTY_VALUES']['BASKET_ITEM_COUNT'] == 0))) {

        if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
            $result = restCommand('imbot.message.add', Array(
                "DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
                "MESSAGE" => 'В корзине еще нет товаров. [send=new_order]Добавить товар[/send]',
            ), $_REQUEST["auth"]);
        } else {
            $result = restCommand('imbot.message.add', Array(
                "DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
                "MESSAGE" => 'В корзине еще нет товаров. [br][br]1: Изменить состав заказа[br]0: Связаться с оператором',
            ), $_REQUEST["auth"]);
        }
		
	} else {

        if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
            $mess = '[b]Корзина товаров:[/b][br]';
        } else {
            $mess = 'Корзина товаров:[br]';
        }

        $basketPrice = 0;
        $refundPrice = 0;
        if ($botSettings['SETTINGS']['REFUND_MESS']) {
            $refundPrice = $botSettings['SETTINGS']['REFUND_MESS'];
        }
        foreach ($resBasket['result'] as $basket) {
            if ($basket['PROPERTY_VALUES']['BASKET_ITEM_COUNT']) {
                $mess .= $basket['NAME'] . ', ';
                $mess .= $basket['PROPERTY_VALUES']['BASKET_ITEM_COUNT'] . ' шт.';
                if ($basket['PROPERTY_VALUES']['BASKET_ITEM_PRICE']) {
                    $mess .= ' по ' . $basket['PROPERTY_VALUES']['BASKET_ITEM_PRICE'] . ' ₽';
                } else {
                    $mess .= ' по ' . $refundPrice . ' ₽';
                }
                $mess .= '[br]';
                $tempBasket1 = $basket['PROPERTY_VALUES']['BASKET_ITEM_PRICE'] * $basket['PROPERTY_VALUES']['BASKET_ITEM_COUNT'];
                $tempBasket2 = $refundPrice * $basket['PROPERTY_VALUES']['BASKET_ITEM_COUNT'];
                if ($basket['PROPERTY_VALUES']['BASKET_ITEM_PRICE']) {
                    $basketPrice += $tempBasket1;
                } else {
                    $basketPrice -= $tempBasket2;
                }
            }
        }

        $saleTrigger = '';
        if ($botSettings['SETTINGS']['DISCOUNT']) {
            foreach ($botSettings['SETTINGS']['DISCOUNT'] as $discountKey => $discountValue) {
                if ($basketPrice >= $discountKey) {
                    $basketPrice = $basketPrice - $basketPrice * $discountValue / 100;
                    $saleTrigger = $discountValue;
                }
                break;
            }
        }

        if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {

            $mess .= '[br]Итоговая стоимость заказа: [b]'.$basketPrice.' ₽[/b]';
            if ($saleTrigger) { $mess .= ' (c учетом скидки '. $saleTrigger .'%)'; }

            if (!empty($resOrder['result'])) {
                if ($resOrder['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS']) {
                    $mess .= '[br]Адрес доставки: [b]' . $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS'] . '[/b]';
                    $mess .= ' [send=add_address][Изменить][/send]';
                } else {
                    $mess .= '[br][send=add_address]Указать адрес доставки[/send]';
                }
                $mess .= '[br]Контактный телефон: [b]' . $messageString . '[/b]';
                $mess .= ' [send=add_phone][Изменить][/send]';
            } else {
                $mess .= '[br][send=add_address]Указать адрес доставки[/send]';
                $mess .= '[br][send=add_phone]Указать телефон[/send]';
            }

            $mess .= '[br]';

            if (!empty($resOrder['result']) && $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS'] && $messageString) {
                $mess .= '[br][send=new_order_fin fin_order]Заказать[/send]';
            }

            $mess .= '[br][send=new_order]Добавить ещё воды и аксессуаров[/send]';
            $mess .= '[br][send=communication]Связаться с оператором[/send]';

        } else {

            $mess .= '[br]Итоговая стоимость заказа: '.$basketPrice.' ₽';
            if ($saleTrigger) { $mess .= ' (c учетом скидки '. $saleTrigger .'%)'; }

            if (!empty($resOrder['result'])) {
                if ($resOrder['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS']) {
                    $mess .= '[br]Адрес доставки: ' . $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS'];
                }
                $mess .= '[br]Контактный телефон: ' . $messageString;
            }

            $mess .= '[br]';

            if (!empty($resOrder['result']) && $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS'] && $messageString) {
                $mess .= '[br]4: Заказать';
                $mess .= '[br]3: Изменить адрес доставки';
            }
            $mess .= '[br]2: Изменить контактный телефон';
            $mess .= '[br]1: Добавить ещё воды и аксессуаров';
            $mess .= '[br]0: Связаться с оператором';

        }
        
	}
	
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
	
	if (($resOrder['result'] || $thisPhone) && $messageString != 'new_phone') {

        if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
            $mess = 'По какому телефону с вами можно связаться?:[br]';
        } else {
            $mess = 'По какому телефону с вами можно связаться?:[br][br]';
        }

		$phoneArray = Array();
		foreach ($resOrder['result'] as $order) {
			if ($order['PROPERTY_VALUES']['ORDER_PHONE'] != $thisPhone) {
				$phoneArray[] = $order['PROPERTY_VALUES']['ORDER_PHONE'];
			}
		}

		$phoneArray = array_unique($phoneArray);
        $phoneCounter = count($phoneArray);

		if ($thisPhone) {
			$phoneCounter++;
		}        
        
		foreach ($phoneArray as $phoneArrayItem) {
            if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
                $mess .= '[send=add_phone ' . $phoneArrayItem . ']' . $phoneArrayItem . '[/send][br]';
            } else {
                $mess .= ($phoneCounter + 4).': '.$phoneArrayItem.'[br]';
            }
            $phoneCounter--;
		}
		
		if ($thisPhone) {
            if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
                $mess .= '[send=add_phone ' . $thisPhone . ']' . $thisPhone . '[/send][br]';
            } else {
                $mess .= '5: '.$thisPhone.'[br]';
            }
			$phoneCounter++;
		}


        if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
            $mess .= '[br][put=add_phone]Указать новый телефон[/put]';
        } else {
        	
        	if (!empty($resOrderN['result']) && $resOrderN['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS'] && $messageString) {
                $mess .= '[br]4: Заказать';
                $mess .= '[br]3: Изменить адрес доставки';
            }
            $mess .= '[br]2: Указать новый телефон';
        	$mess .= '[br]1: Добавить ещё воды и аксессуаров';
            $mess .= '[br]0: Связаться с оператором';
            
        }
	
	} else {
		
		if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
			$mess = '[put=add_phone]Укажите контактный телефон[/put]';
		} else {
        	$mess = 'Укажите контактный телефон:';
        }
		
	}
	
}

if ($chatType['0'] != 'livechat' && $chatType['0'] != 'network') {
	$dialogSettings['SETTINGS']['PARAM_COMMAND'] = '';
	saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);
}

$result = restCommand('imbot.message.add', Array(
	"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
	"MESSAGE" => $mess,
), $_REQUEST["auth"]);