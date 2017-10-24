<?php

$chatType = explode('|', $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID']);

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

if ($messageArray['0'] && $messageArray['1']) {
	
	$updateStatus = 0;
	$updateID = 0;
	$updateKey = 0;
	$updateCount = 0;
	foreach ($resBasket['result'] as $basketKey => $basketItem) {
		if (($basketItem['PROPERTY_VALUES']['BASKET_ITEM_ID'] == $messageArray['0']) && ($basketItem['PROPERTY_VALUES']['BASKET_TYPE'] == '1')) {
			$updateStatus = 1;
			$updateID = $basketItem['ID'];
			$updateKey = $basketKey;
			$updateCount = $basketItem['PROPERTY_VALUES']['BASKET_ITEM_COUNT'];
		}
	}
	
	$resWater = restCommand('entity.item.get', Array(
		'ENTITY' => CATALOG_CODE,
		'FILTER' => array(
			'ID' => $messageArray['0'],
		),
		'SORT' => array(
			'DATE_ACTIVE_FROM' => 'ASC',
			'ID' => 'ASC',
		),
	), $_REQUEST['auth']);
	
	foreach ($resWater['result'] as $water) {
		if ($updateStatus) {

			$newCount = $updateCount + $messageArray['1'];
			$resBasketUpdate = restCommand('entity.item.update', Array(
				'ENTITY' => BASKET_CODE,
				'ID' => $updateID,
				'NAME' => $water['NAME'],
				'PROPERTY_VALUES' => array(
					'BASKET_USER_ID' => $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID'],
					'BASKET_ITEM_ID' => $water['ID'],
					'BASKET_ITEM_COUNT' => $newCount,
					'BASKET_ITEM_VOLUME' => $water['PROPERTY_VALUES']['PRODUCT_VOLUME'],
					'BASKET_ITEM_PRICE' => $water['PROPERTY_VALUES']['PRODUCT_PRICE'],
				),
			), $_REQUEST['auth']);
			
			$resBasket['result'][$updateKey]['NAME'] = $water['NAME'];
			$resBasket['result'][$updateKey]['PROPERTY_VALUES']['BASKET_ITEM_COUNT'] = $newCount;
			$resBasket['result'][$updateKey]['PROPERTY_VALUES']['BASKET_ITEM_VOLUME'] = $water['PROPERTY_VALUES']['PRODUCT_VOLUME'];
			$resBasket['result'][$updateKey]['PROPERTY_VALUES']['BASKET_ITEM_PRICE'] = $water['PROPERTY_VALUES']['PRODUCT_PRICE'];
			
		} else {
			
			$resBasketAdd = restCommand('entity.item.add', Array(
				'ENTITY' => BASKET_CODE,
				'NAME' => $water['NAME'],
				'PROPERTY_VALUES' => array(
					'BASKET_USER_ID' => $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID'],
					'BASKET_ITEM_ID' => $water['ID'],
					'BASKET_ITEM_COUNT' => $messageArray['1'],
					'BASKET_ITEM_VOLUME' => $water['PROPERTY_VALUES']['PRODUCT_VOLUME'],
					'BASKET_ITEM_PRICE' => $water['PROPERTY_VALUES']['PRODUCT_PRICE'],
					'BASKET_TYPE' => '1',
				),
			), $_REQUEST['auth']);
			
			$tempBasketItem = array();
			$tempBasketItem['NAME'] = $water['NAME'];
			$tempBasketItem['PROPERTY_VALUES']['BASKET_ITEM_COUNT'] = $messageArray['1'];
			$tempBasketItem['PROPERTY_VALUES']['BASKET_ITEM_VOLUME'] = $water['PROPERTY_VALUES']['PRODUCT_VOLUME'];
			$tempBasketItem['PROPERTY_VALUES']['BASKET_ITEM_PRICE'] = $water['PROPERTY_VALUES']['PRODUCT_PRICE'];
			$tempBasketItem['PROPERTY_VALUES']['BASKET_USER_ID'] = $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID'];
			$tempBasketItem['PROPERTY_VALUES']['BASKET_ITEM_ID'] = $water['ID'];
			$resBasket['result'][] = $tempBasketItem;
			
		}
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

		if ($botSettings['SETTINGS']['REFUND_MESS']) {
			if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
				$messCommand = '[send=new_order_refund]Выбрать тару на возврат[/send]';
			} else {
				$messCommand = '3: Выбрать тару на возврат[br]';
			}
		} elseif (!empty($resAccessories['result'])) {
			if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
				$messCommand = '[send=new_order_accessories]Выбрать аксессуары[/send]';
			} else {
				$messCommand = '3: Выбрать аксессуары[br]';
			}
		} else {
			if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
				
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
				
				
			} else {

				$messCommand = '3: Указать адрес доставки[br]';
				
				if ($botSettings['SETTINGS']['DELIVERY_MESS']) {
					$messAdditional = '[br][br]'.$botSettings['SETTINGS']['DELIVERY_MESS'];
				}
				
			}
		}

		if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
			$messCommand .= '[br][send=new_order]Добавить ещё воды[/send]';
			$messCommand .= '[br][send=communication]Связаться с оператором[/send]';
			$mess = '[b]Корзина товаров:[/b][br]';
		} else {
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
