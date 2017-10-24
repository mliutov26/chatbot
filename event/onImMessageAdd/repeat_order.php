<?php

$chatType = explode('|', $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID']);

$activeOrder = restCommand('entity.item.get', Array(
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

if (!empty($activeOrder['result'])) {
	
	$mess = 'Необходимо сначала оформить активный заказ до конца';

    if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
        $mess .= '[br][br][send=new_order_fin]Посмотреть активный заказ[/send]';
        $mess .= '[br][send=new_order]Изменить состав заказа[/send]';
        $mess .= '[br][send=communication]Связаться с оператором[/send]';
    } else {
        $mess .= '[br][br]2: Посмотреть активный заказ';
        $mess .= '[br]1: Изменить состав заказа';
        $mess .= '[br]0: Связаться с оператором';
    }

} else {
	
	$resOrder = restCommand('entity.item.get', Array(
		'ENTITY' => ORDER_CODE,
		'FILTER' => array(
			'PROPERTY_ORDER_USER_ID' => $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID'],
			'PROPERTY_ORDER_STATUS' => 'Y',
		),
		'SORT' => array(
			'DATE_ACTIVE_FROM' => 'DESC',
			'ID' => 'DESC',
		),
	), $_REQUEST['auth']);
	
	if ($resOrder['result']) {
		
		$resOrderAdd = restCommand('entity.item.add', Array(
			'ENTITY' => ORDER_CODE,
			'NAME' => 'Заказ от '.date("Y-m-d H:i:s"),
			'PROPERTY_VALUES' => array(
				'ORDER_ADRESS' => $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS'],
				'ORDER_PHONE' => $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_PHONE'],
				'ORDER_STATUS' => 'N',
				'ORDER_USER_ID' => $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID'],
			),
		), $_REQUEST['auth']);
		
		$newBasket = unserialize($resOrder['result']['0']['PROPERTY_VALUES']['ORDER_ITEMS']);
		
		if ($newBasket) {

            if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
                $mess = '[b]Корзина товаров:[/b][br]';
            } else {
                $mess = 'Корзина товаров:[br]';
            }
			$basketPrice = 0;
			$refundPrice = 0;
			if ($botSettings['SETTINGS']['REFUND_MESS']) { $refundPrice = $botSettings['SETTINGS']['REFUND_MESS']; }
			foreach ($newBasket as $newBasketItem) {
				$newBasketAdd = restCommand('entity.item.add', Array(
					'ENTITY' => BASKET_CODE,
					'NAME' => $newBasketItem['NAME'],
					'PROPERTY_VALUES' => array(
						'BASKET_USER_ID' => $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID'],
						'BASKET_ITEM_ID' => $newBasketItem['PROPERTY_VALUES']['BASKET_ITEM_ID'],
						'BASKET_ITEM_COUNT' => $newBasketItem['PROPERTY_VALUES']['BASKET_ITEM_COUNT'],
						'BASKET_ITEM_VOLUME' => $newBasketItem['PROPERTY_VALUES']['BASKET_ITEM_VOLUME'],
						'BASKET_ITEM_PRICE' => $newBasketItem['PROPERTY_VALUES']['BASKET_ITEM_PRICE'],
						'BASKET_TYPE' => $newBasketItem['PROPERTY_VALUES']['BASKET_TYPE'],
					),
				), $_REQUEST['auth']);
				if ($newBasketItem['PROPERTY_VALUES']['BASKET_ITEM_COUNT']) {
					$mess .= $newBasketItem['NAME'].', ';
					$mess .= $newBasketItem['PROPERTY_VALUES']['BASKET_ITEM_COUNT'].' шт.';
					if ($newBasketItem['PROPERTY_VALUES']['BASKET_ITEM_PRICE']) {
						$mess .= ' по '.$newBasketItem['PROPERTY_VALUES']['BASKET_ITEM_PRICE'].' ₽';
					} else {
						$mess .= ' по '.$refundPrice.' ₽';		
					}
					$mess .= '[br]';
					$tempBasket1 = $newBasketItem['PROPERTY_VALUES']['BASKET_ITEM_PRICE'] * $newBasketItem['PROPERTY_VALUES']['BASKET_ITEM_COUNT'];
					$tempBasket2 = $refundPrice * $newBasketItem['PROPERTY_VALUES']['BASKET_ITEM_COUNT'];
					if ($newBasketItem['PROPERTY_VALUES']['BASKET_ITEM_PRICE']) { $basketPrice += $tempBasket1; } else { $basketPrice -= $tempBasket2; }
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

                if ($resOrder['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS']) {
                	$mess .= '[br]Адрес доставки: [b]' . $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS'] . '[/b]';
                    $mess .= ' [send=add_address][Изменить][/send]';
                }
                if ($resOrder['result']['0']['PROPERTY_VALUES']['ORDER_PHONE']) {
                    $mess .= '[br]Контактный телефон: [b]' . $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_PHONE'] . '[/b]';
					$mess .= ' [send=add_phone][Изменить][/send]';
                }

                $mess .= '[br][br][send=new_order_fin fin_order]Повторить заказ[/send]';
                $mess .= '[br][send=new_order]Добавить ещё воды и аксессуаров[/send]';
                $mess .= '[br][send=communication]Связаться с оператором[/send]';

            } else {

                $dialogSettings['SETTINGS']['PARAM_COMMAND'] = 'fin_order';
                saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

                $mess .= '[br]Итоговая стоимость заказа: '.$basketPrice.' ₽';
                if ($saleTrigger) { $mess .= ' (c учетом скидки '. $saleTrigger .'%)'; }

                if ($resOrder['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS']) {
                    $mess .= '[br]Адрес доставки: ' . $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS'] . '.';
                }
                if ($resOrder['result']['0']['PROPERTY_VALUES']['ORDER_PHONE']) {
                    $mess .= '[br]Контактный телефон: ' . $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_PHONE'] . '.';
                }

                $mess .= '[br][br]2: Повторить заказ';

	            /*if (!$resOrder['result']['0']['PROPERTY_VALUES']['ORDER_PHONE']) {
	                $mess .= '[br]3: Указать телефон';
	            } else {
	                $mess .= '[br]3: Изменить контактный телефон';
	            }
	            if (!$resOrder['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS']) {
	                $mess .= '[br]2: Указать адрес доставки';
	            } else {
	                $mess .= '[br]2: Изменить адрес доставки';
	            }*/             
                
                $mess .= '[br]1: Изменить состав заказа';
                $mess .= '[br]0: Связаться с оператором';

            }
			
		} else {
			
			$mess = 'У последнего заказа не найдены товары';
			$mess .= '[br][br][send=communication]Связаться с оператором[/send]';
			
		}
	
	} else {
		
		$mess = 'Не найдено старых заказов';
        if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
            $mess .= '[br][br][send=new_order]Создать новый заказ[/send]';
            $mess .= '[br][send=communication]Связаться с оператором[/send]';
        } else {
            $mess .= '[br][br]1: Создать новый заказ';
            $mess .= '[br]0: Связаться с оператором';
        }
		
	}
	
}

$result = restCommand('imbot.message.add', Array(
	"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
	"MESSAGE" => $mess,
), $_REQUEST["auth"]);