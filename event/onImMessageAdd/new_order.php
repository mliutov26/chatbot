<?php

$chatType = explode('|', $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID']);

$resWater = restCommand('entity.item.get', Array(
	'ENTITY' => CATALOG_CODE,
	'SORT' => array(
		'DATE_ACTIVE_FROM' => 'ASC',
		'ID' => 'ASC',
	),
), $_REQUEST['auth']);

if (array_key_exists('error', $resWater) && !empty($resWater['error'])) {
	
	$result = restCommand('imbot.message.add', Array(
		"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
		"MESSAGE" => 'Ой, что-то пошло не так. :( [br][br] Не удалось загрузить товары',
	), $_REQUEST["auth"]);

} else {
	
	if(empty($resWater['result'])) {
		
		if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
			$result = restCommand('imbot.message.add', Array(
				"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
				"MESSAGE" => 'В каталоге воды еще нет товаров[br][br][send=communication]Связаться с оператором[/send]',
			), $_REQUEST["auth"]);
		} else {
			$result = restCommand('imbot.message.add', Array(
				"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
				"MESSAGE" => 'В каталоге воды еще нет товаров[br][br]0: Связаться с оператором',
			), $_REQUEST["auth"]);	
		}
		
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
				$messCommand = '1: Выбрать тару на возврат[br]';
			}
		} elseif (!empty($resAccessories['result'])) {
			if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
				$messCommand = '[send=new_order_accessories]Выбрать аксессуары[/send]';
			} else {
				$messCommand = '1: Выбрать аксессуары[br]';
			}
		} else {
			if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
				//$messCommand = '[send=see_delivery]Узнать условия доставки[/send]';
				
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
					$messAdditional = $botSettings['SETTINGS']['DELIVERY_MESS'].'[br][br]';
				}
				
			} else {
				
				$messCommand = '1: Указать адрес доставки[br]';
				
				if ($botSettings['SETTINGS']['DELIVERY_MESS']) {
					$messAdditional = $botSettings['SETTINGS']['DELIVERY_MESS'].'[br][br]';
				}
				
			}
		}

		if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
			$messCommand .= '[br][send=communication]Связаться с оператором[/send]';
		} else {
			$messCommand .= '0: Связаться с оператором';
		}
		
		if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
			$mess = '[b]Каталог воды:[/b]';
		} else {
			$mess = 'Выберите воду:[br][br]';
		}
		
		$waterCount = 0;
		foreach ($resWater['result'] as $water) {
			$resBasket = restCommand('entity.item.get', Array(
				'ENTITY' => BASKET_CODE,
				'FILTER' => array(
					'PROPERTY_BASKET_USER_ID' => $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID'],
					'PROPERTY_BASKET_ITEM_ID' => $water['ID'],
					'PROPERTY_BASKET_TYPE' => '1',
				),
				'SORT' => array(
					'DATE_ACTIVE_FROM' => 'ASC',
					'ID' => 'ASC',
				),
			), $_REQUEST['auth']);
			
			if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
				
				$mess .= $water['PREVIEW_PICTURE'].'[br]'.'[b]Выбрать:[/b] ';
				if ($resBasket['result']) {
					$mess .= '[send=delete_basket '.$water['ID'].'](X)[/send] ';	
				}
				$mess .= '[send=add_basket '.$water['ID'].'_1]1[/send] ';
				$mess .= '[send=add_basket '.$water['ID'].'_2]2[/send] ';
				$mess .= '[send=add_basket '.$water['ID'].'_3]3[/send] ';
				$mess .= '[send=add_basket '.$water['ID'].'_4]4[/send] ';
				$mess .= '[send=add_basket '.$water['ID'].'_5]5[/send] ';
				$mess .= '[send=add_basket '.$water['ID'].'_6]6[/send] ';
				$mess .= '[br]';
				
			} else {

				$mess .= $water['NAME'].' ('.$water['PROPERTY_VALUES']['PRODUCT_VOLUME'].' л., '.$water['PROPERTY_VALUES']['PRODUCT_PRICE'].' ₽)'.'[br]';
				$mess .= (count($resWater['result']) + 1 - $waterCount).'1: Купить 1 шт.'.'[br]';
                $mess .= (count($resWater['result']) + 1 - $waterCount).'2: Купить 2 шт.'.'[br]';
                $mess .= (count($resWater['result']) + 1 - $waterCount).'3: Купить 3 шт.'.'[br]';
                $mess .= (count($resWater['result']) + 1 - $waterCount).'4: Купить 4 шт.'.'[br]';
                $mess .= (count($resWater['result']) + 1 - $waterCount).'5: Купить 5 шт.'.'[br]';
                $mess .= (count($resWater['result']) + 1 - $waterCount).'6: Купить 6 шт.'.'[br]';

            }
            $mess .= '[br]';
			$waterCount++;
		}
		
		if ($messAdditional) {
			$mess .= $messAdditional;
		}
		
		if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
			$result = restCommand('imbot.message.add', Array(
				"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
				"MESSAGE" => $mess.$messCommand,
			), $_REQUEST["auth"]);
		} else {
			$result = restCommand('imbot.message.add', Array(
				"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
				"MESSAGE" => $mess.$messCommand,
			), $_REQUEST["auth"]);
		}
		
	}
	
}
