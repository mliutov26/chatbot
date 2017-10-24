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

$resOrder = restCommand('entity.item.get', Array(
	'ENTITY' => ORDER_CODE,
	'FILTER' => array(
		'PROPERTY_ORDER_USER_ID' => $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID'],
		'PROPERTY_ORDER_STATUS' => 'N',
	),
	'SORT' => array(
		'DATE_ACTIVE_FROM' => 'DESC',
		'ID' => 'DESC',
	),
), $_REQUEST['auth']);

if ($chatType['0'] != 'livechat' && $chatType['0'] != 'network') {
    $messageString = $dialogSettings['SETTINGS']['PARAM_COMMAND'];
}

if ($messageString == 'fin_order') {
	
	if (!empty($resBasket['result']) && !empty($resOrder['result']) && $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS'] && $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_PHONE']) {
		
		$basketArray = Array();
	    $basketText = '';
        $basketPrice = 0;
        $refundPrice = 0;
        if ($botSettings['SETTINGS']['REFUND_MESS']) { $refundPrice = $botSettings['SETTINGS']['REFUND_MESS']; }

		foreach ($resBasket['result'] as $basket) {
			$basketArray[] = $basket;

			if ($basketText) { $basketText .= ', '; }
            $basketText .= $basket['NAME'];
            if ($basket['PROPERTY_VALUES']['BASKET_ITEM_COUNT']) { $basketText .= ' '.$basket['PROPERTY_VALUES']['BASKET_ITEM_COUNT'].' шт.'; }
            if ($basket['PROPERTY_VALUES']['BASKET_ITEM_PRICE']) { $basketText .= ' по '.$basket['PROPERTY_VALUES']['BASKET_ITEM_PRICE'].' ₽'; }

            $tempBasket1 = $basket['PROPERTY_VALUES']['BASKET_ITEM_PRICE'] * $basket['PROPERTY_VALUES']['BASKET_ITEM_COUNT'];
            $tempBasket2 = $refundPrice * $basket['PROPERTY_VALUES']['BASKET_ITEM_COUNT'];
            if ($basket['PROPERTY_VALUES']['BASKET_ITEM_PRICE']) { $basketPrice += $tempBasket1; } else { $basketPrice -= $tempBasket2; }

			$resBasketDelete = restCommand('entity.item.delete', Array(
				'ENTITY' => BASKET_CODE,
				'ID' => $basket['ID'],
			), $_REQUEST['auth']);
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
		
		$resOrderUpdate = restCommand('entity.item.update', Array(
			'ENTITY' => ORDER_CODE,
			'ID' => $resOrder['result']['0']['ID'],
			'PROPERTY_VALUES' => array(
				'ORDER_STATUS' => 'Y',
				'ORDER_ITEMS' => serialize($basketArray),
			),
		), $_REQUEST['auth']);
		
		$newRequest = $_REQUEST['auth'];

		$mysqli = mysqli_connect(SQL_SERVER, SQL_LOGIN, SQL_PASSWORD, SQL_TABLE);
		if ($mysqli) {
			$selectQuery = mysqli_query($mysqli, 'SELECT * FROM cn_mega_portal_reg WHERE APP_CODE = "waterbot" AND URL = "'.$_REQUEST['auth']['domain'].'"');
			if (mysqli_num_rows($selectQuery)) {
				
				while($selectQueryRow = $selectQuery->fetch_assoc()) {
					$newRequest['access_token'] = $selectQueryRow['ACCESS_TOKEN'];
					$newRequest['expires_in'] = $selectQueryRow['EXPIRES_IN'];
					$newRequest['refresh_token'] = $selectQueryRow['REFRESH_TOKEN'];
					$newRequest['member_id'] = $selectQueryRow['MEMBER_ID'];
					$newRequest['user_id'] = $selectQueryRow['EXT_MODIFIED_BY'];
				}

                $leadAssignedBy = 1;
				if (!empty($botSettings['SETTINGS']['MANAGERS'])) {
                    foreach ($botSettings['SETTINGS']['MANAGERS'] as $managerKey => $managerValue) {
                        $leadAssignedBy = $managerKey;
                    }
                }
				
				$leadAdd = restCommand('crm.lead.add', Array(
					'fields' => Array(
						'TITLE' => 'Заказ воды по адресу: '.$resOrder['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS'],
						'STATUS_ID' => 'NEW',
			            'OPENED' => 'Y', 
			            'ASSIGNED_BY_ID' => $leadAssignedBy,
			            'PHONE' => Array(
			            	Array(
			            		'VALUE' => $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_PHONE'],
			            		'VALUE_TYPE'=>'WORK'
			            	),
			            ),
                        'ADDRESS' => $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS'],
                        'COMMENTS' => $basketText,
                        'CURRENCY_ID' => 'RUB',
                        'OPPORTUNITY' => $basketPrice,

			        ),
				), $newRequest);

                if ($botSettings['SETTINGS']['THANKS_MESS']) {
				    $mess = $botSettings['SETTINGS']['THANKS_MESS'];
                } else {
                    $mess = 'Заказ оформлен';
                }

                if ($chatType['0'] != 'livechat' && $chatType['0'] != 'network') {
                    $dialogSettings['SETTINGS']['PARAM_COMMAND'] = '';
                    $dialogSettings['SETTINGS']['PARAM'] = '';
                    $dialogSettings['SETTINGS']['STATE'] = '';
                    saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);
                }
				
			} else {
				
				$mess = 'В процессе оформления заказа что-то пошло не так.';
                if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
                    $mess .= '[br][send=communication]Связаться с оператором[/send]';
                } else {
                    $mess .= '[br]0: Связаться с оператором';
                }
				
			}
			mysqli_close($selectQuery);	
		}
	
	} else {
		
		$mess = 'В процессе оформления заказа что-то пошло не так.';
        if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
            $mess .= '[br][send=new_order]Изменить состав заказа[/send]';
            $mess .= '[br][send=communication]Связаться с оператором[/send]';
        } else {
            $mess .= '[br]0: Связаться с оператором';
        }
		
	}

} else {
	
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

            if (!empty($resOrder['result']) && $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS'] && $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_PHONE']) {
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
                if ($resOrder['result']['0']['PROPERTY_VALUES']['ORDER_PHONE']) {
                    $mess .= '[br]Контактный телефон: ' . $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_PHONE'];
                }
            }

            $mess .= '[br]';

            if (!empty($resOrder['result']) && $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS'] && $resOrder['result']['0']['PROPERTY_VALUES']['ORDER_PHONE']) {
                $mess .= '[br]4: Заказать';
            }

            if (!$resOrder['result']['0']['PROPERTY_VALUES']['ORDER_PHONE']) {
                $mess .= '[br]3: Указать телефон';
            } else {
                $mess .= '[br]3: Изменить контактный телефон';
            }
            if (!$resOrder['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS']) {
                $mess .= '[br]2: Указать адрес доставки';
            } else {
                $mess .= '[br]2: Изменить адрес доставки';
            }
            $mess .= '[br]1: Добавить ещё воды и аксессуаров';
            $mess .= '[br]0: Связаться с оператором';

        }
		
	}

}

$result = restCommand('imbot.message.add', Array(
	"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
	"MESSAGE" => $mess,
), $_REQUEST["auth"]);
