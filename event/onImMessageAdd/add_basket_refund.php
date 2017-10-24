<?php

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

if ($chatType['0'] != 'livechat' && $chatType['0'] != 'network') {
    $messageArray = explode('_', $dialogSettings['SETTINGS']['PARAM_COMMAND']);
}

if ($messageArray['0']) {
	
	$updateStatus = 0;
	$updateID = 0;
	$updateKey = 0;
	foreach ($resBasket['result'] as $basketKey => $basketItem) {
		if ($basketItem['PROPERTY_VALUES']['BASKET_TYPE'] == '2') {
			$updateStatus = 1;
			$updateID = $basketItem['ID'];
			$updateKey = $basketKey;
		}
	}
	
	if ($updateStatus) {

		$resBasketUpdate = restCommand('entity.item.update', Array(
			'ENTITY' => BASKET_CODE,
			'ID' => $updateID,
			'PROPERTY_VALUES' => array(
				'BASKET_ITEM_COUNT' => $messageArray['0'],
			),
		), $_REQUEST['auth']);
		
		$resBasket['result'][$updateKey]['PROPERTY_VALUES']['BASKET_ITEM_COUNT'] = $messageArray['0'];
		
	} else {
		
		$resBasketAdd = restCommand('entity.item.add', Array(
			'ENTITY' => BASKET_CODE,
			'NAME' => 'Возврат пустых бутылок',
			'PROPERTY_VALUES' => array(
				'BASKET_USER_ID' => $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID'],
				'BASKET_ITEM_COUNT' => $messageArray['0'],
				'BASKET_TYPE' => '2',
			),
		), $_REQUEST['auth']);
		
		$tempBasketItem = array();
		$tempBasketItem['NAME'] = 'Возврат пустых бутылок';
		$tempBasketItem['PROPERTY_VALUES']['BASKET_ITEM_COUNT'] = $messageArray['0'];
		$tempBasketItem['PROPERTY_VALUES']['BASKET_USER_ID'] = $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID'];
		$resBasket['result'][] = $tempBasketItem;
		
	}

}

if (array_key_exists('error', $resBasket) && !empty($resBasket['error'])) {
	
	$result = restCommand('imbot.message.add', Array(
		"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
		"MESSAGE" => 'Ой, что-то пошло не так. :( [br] Не удалось загрузить корзину',
	), $_REQUEST["auth"]);

} else {

	if((empty($resBasket['result'])) || (($resBasket['total'] == 1) && ($resBasket['result']['0']['PROPERTY_VALUES']['BASKET_ITEM_COUNT'] == 0))) {
		
		$result = restCommand('imbot.message.add', Array(
			"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
			"MESSAGE" => 'В корзине еще нет товаров. [send=new_order]Добавить товар[/send]',
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
                $messCommand = '[send=new_order_accessories]Выбрать аксессуары[/send]';
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
					$messAdditional = '[br][br]'.$botSettings['SETTINGS']['DELIVERY_MESS'];
				}
            	
            }
            $messCommand .= '[br][send=new_order_refund]Указать тару на возврат[/send]';
            $messCommand .= '[br][send=new_order]Добавить ещё воды[/send]';
            $messCommand .= '[br][send=communication]Связаться с оператором[/send]';
            $mess = '[b]Корзина товаров:[/b][br]';

        } else {

            if (!empty($resAccessories['result'])) {
                $messCommand = '4: Выбрать аксессуары[br]';
            } else {
				$messCommand = '4: Указать адрес доставки[br]';
				if ($botSettings['SETTINGS']['DELIVERY_MESS']) {
					$messAdditional = '[br][br]'.$botSettings['SETTINGS']['DELIVERY_MESS'];
				}
            }
            $messCommand .= '3: Указать тару на возврат[br]';
            $messCommand .= '2: Добавить ещё воды[br]';
            $messCommand .= '1: Очистить корзину[br]';
            $messCommand .= '0: Связаться с оператором';
            $mess = 'Корзина товаров:[br]';

        }

		$basketPrice = 0;
		$refundPrice = 0;
		if ($botSettings['SETTINGS']['REFUND_MESS']) { $refundPrice = $botSettings['SETTINGS']['REFUND_MESS']; }
		foreach ($resBasket['result'] as $basket) {
			if ($basket['PROPERTY_VALUES']['BASKET_ITEM_COUNT']) {
				$mess .= $basket['NAME'].', ';
				$mess .= $basket['PROPERTY_VALUES']['BASKET_ITEM_COUNT'].' шт.';
				if ($basket['PROPERTY_VALUES']['BASKET_ITEM_PRICE']) {
					$mess .= ' по '.$basket['PROPERTY_VALUES']['BASKET_ITEM_PRICE'].' ₽';
				} else {
					$mess .= ' по '.$refundPrice.' ₽';		
				}
				$mess .= '[br]';
				$tempBasket1 = $basket['PROPERTY_VALUES']['BASKET_ITEM_PRICE'] * $basket['PROPERTY_VALUES']['BASKET_ITEM_COUNT'];
				$tempBasket2 = $refundPrice * $basket['PROPERTY_VALUES']['BASKET_ITEM_COUNT'];
				if ($basket['PROPERTY_VALUES']['BASKET_ITEM_PRICE']) { $basketPrice += $tempBasket1; } else { $basketPrice -= $tempBasket2; }
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
        } else {
            $mess .= '[br]Итоговая стоимость заказа: '.$basketPrice.' ₽';
            if ($saleTrigger) { $mess .= ' (c учетом скидки '. $saleTrigger .'%)'; }
        }
        
	   	if ($messAdditional) {
			$mess .= $messAdditional;
		}
		
		if ($chatType['0'] != 'livechat' && $chatType['0'] != 'network') {
			$dialogSettings['SETTINGS']['PARAM_COMMAND'] = '';
			saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);
		}
		
		$result = restCommand('imbot.message.add', Array(
			"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
			"MESSAGE" => $mess.'[br][br]'.$messCommand,
		), $_REQUEST["auth"]);
		
	}
	
}
