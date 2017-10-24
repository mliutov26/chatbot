<?php

$chatType = explode('|', $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID']);

if ($chatType['0'] != 'livechat' && $chatType['0'] != 'network') {
    $messageString = $dialogSettings['SETTINGS']['PARAM_COMMAND'];
}

$resOrderY = restCommand('entity.item.get', Array(
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

$resOrder = restCommand('entity.item.get', Array(
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

$thisAdress = $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS'];

if ($messageString && $messageString != 'new_address') {
	
	if(empty($resOrder['result'])) {
		$resOrderAdd = restCommand('entity.item.add', Array(
			'ENTITY' => ORDER_CODE,
			'NAME' => 'Заказ от '.date("Y-m-d H:i:s"),
			'PROPERTY_VALUES' => array(
				'ORDER_ADRESS' => $messageString,
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
					'ORDER_ADRESS' => $messageString,
					'ORDER_STATUS' => 'N',
				),
			), $_REQUEST['auth']);
		}
	}
	
	/*	
	if ($resOrder['result']['0']['PROPERTY_VALUES']['ORDER_PHONE']) {
			
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
	                $mess .= '[br]Адрес доставки: [b]' . $messageString . '[/b]';
	                $mess .= ' [send=add_address][Изменить][/send]';
	                if ($resOrder['result']['0']['PROPERTY_VALUES']['ORDER_PHONE']) {
	                    $mess .= '[br]Контактный телефон: [b]' . $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_PHONE'] . '[/b]';
	                    $mess .= ' [send=add_phone][Изменить][/send]';
	                } else {
	                    $mess .= '[br][send=add_phone]Указать телефон[/send]';
	                }
	            } else {
	                $mess .= '[br][send=add_address]Указать адрес доставки[/send]';
	                $mess .= '[br][send=add_phone]Указать телефон[/send]';
	            }
	
	            $mess .= '[br]';
	
	            if (!empty($resOrder['result']) && $messageString && $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_PHONE']) {
	                $mess .= '[br][send=new_order_fin fin_order]Заказать[/send]';
	            }
	
	            $mess .= '[br][send=new_order]Добавить ещё воды и аксессуаров[/send]';
	            $mess .= '[br][send=communication]Связаться с оператором[/send]';
	
	        } else {
	
	            $mess .= '[br]Итоговая стоимость заказа: '.$basketPrice.' ₽';
	            if ($saleTrigger) { $mess .= ' (c учетом скидки '. $saleTrigger .'%)'; }
	
	            if (!empty($resOrder['result'])) {
	                $mess .= '[br]Адрес доставки: ' . $messageString;
	                if ($resOrder['result']['0']['PROPERTY_VALUES']['ORDER_PHONE']) {
	                    $mess .= '[br]Контактный телефон: ' . $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_PHONE'];
	                }
	            }
	
	            $mess .= '[br]';
	
	            $mess .= '[br]3: Указать контактный телефон';
	            $mess .= '[br]2: Изменить адрес доставки';
	            $mess .= '[br]1: Добавить ещё воды и аксессуаров';
	            $mess .= '[br]0: Связаться с оператором';
	
	        }
			
		}
	
		
	} else {*/
		
		$mess = 'Мы доставим заказ по адресу: '.$messageString;
		
		if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
			if ($resOrderY['result']) {
				$mess .= '[br][br][send=add_phone]Указать контактный телефон[/send]';
			} else {
				$mess .= '[br][br][put=add_phone]Указать контактный телефон[/put]';				
			}
	        $mess .= '[br][send=new_order]Добавить ещё воды и аксессуаров[/send]';
	        $mess .= '[br][send=communication]Связаться с оператором[/send]';
	    } else {
	    	
	    	$mess .= '[br]';
 
			$mess .= '[br]3: Указать контактный телефон';
			$mess .= '[br]2: Изменить адрес доставки';
	        $mess .= '[br]1: Добавить ещё воды и аксессуаров';
	        $mess .= '[br]0: Связаться с оператором';
	    }
	
	/*}*/

} else {
	
	if (($thisAdress || $resOrderY['result']) && $messageString != 'new_address') {

        if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
            $mess = 'Куда доставить заказ?[br]';
        } else {
            $mess = 'Куда доставить заказ?[br][br]';
        }

		$addressArray = Array();
		foreach ($resOrderY['result'] as $order) {
			if ($order['PROPERTY_VALUES']['ORDER_ADRESS'] != $thisAdress) {
				$addressArray[] = $order['PROPERTY_VALUES']['ORDER_ADRESS'];
			}
		}

		$addressArray = array_unique($addressArray);
		$adressCounter = count($addressArray);
		if ($thisAdress) {
			$adressCounter++;
		}
		foreach ($addressArray as $addressArrayItem) {
            if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
                $mess .= '[send=add_address ' . $addressArrayItem . ']' . $addressArrayItem . '[/send][br]';
            } else {
                $mess .= ($adressCounter + 3).': '.$addressArrayItem.'[br]';
            }
            $adressCounter--;
		}
		
		if ($thisAdress) {
            if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
                $mess .= '[send=add_address ' . $thisAdress . ']' . $thisAdress . '[/send][br]';
            } else {
                $mess .= '4: '.$thisAdress.'[br]';
            }
			$adressCounter++;
		}

        if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
		    $mess .= '[br][put=add_address]Указать новый адрес[/put]';
		} else {
            $mess .= '[br]2: Указать новый адрес';
            $mess .= '[br]1: Добавить ещё воды и аксессуаров';
            $mess .= '[br]0: Связаться с оператором';
        }

	} else {

        if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
            $mess = '[put=add_address]Укажите адрес доставки[/put]';
        } else {
        	$mess = 'Укажите адрес доставки:';
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