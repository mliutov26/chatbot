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

if ($messageArray['0']) {
	foreach ($resBasket['result'] as $basketKey => $basketValue) {
		if (($basketValue['PROPERTY_VALUES']['BASKET_ITEM_ID'] == $messageArray['0']) && ($basketValue['PROPERTY_VALUES']['BASKET_TYPE'] == '1')) {
			
			$resBasketDelete = restCommand('entity.item.delete', Array(
				'ENTITY' => BASKET_CODE,
				'ID' => $basketValue['ID']
			), $_REQUEST['auth']);
			
			$resBasket['result'][$basketKey]['PROPERTY_VALUES']['BASKET_ITEM_COUNT'] = 0;
			
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
			$messCommand = '[send=new_order_refund]Указать тару на возврат[/send]';
		} elseif (!empty($resAccessories['result'])) {
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
		
		$messCommand .= '[br][send=new_order]Добавить ещё воды[/send]';
		$messCommand .= '[br][send=communication]Связаться с оператором[/send]';
						
		$mess = '[b]Корзина товаров:[/b][br]';
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
		$mess .= '[br]Итоговая стоимость заказа: [b]'.$basketPrice.' ₽[/b]';
		
		if ($messAdditional) {
			$mess .= $messAdditional;
		}
		
		$result = restCommand('imbot.message.add', Array(
			"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
			"MESSAGE" => $mess.'[br][br]'.$messCommand,
		), $_REQUEST["auth"]);
		
	}
	
}
