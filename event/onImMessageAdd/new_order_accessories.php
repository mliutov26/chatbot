<?php

$chatType = explode('|', $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID']);

$resWater = restCommand('entity.item.get', Array(
	'ENTITY' => CAT_DETAILS,
	'SORT' => array(
		'DATE_ACTIVE_FROM' => 'ASC',
		'ID' => 'ASC',
	),
), $_REQUEST['auth']);

if (array_key_exists('error', $resWater) && !empty($resWater['error'])) {
	
	$result = restCommand('imbot.message.add', Array(
		"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
		"MESSAGE" => 'Ой, что-то пошло не так. :( [br] Не удалось загрузить товары',
	), $_REQUEST["auth"]);

} else {

	if(empty($resWater['result'])) {
		
		$result = restCommand('imbot.message.add', Array(
			"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
			"MESSAGE" => 'В каталоге аксессуаров еще нет товаров.[br][send=communication]Связаться с оператором[/send]',
		), $_REQUEST["auth"]);

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
				$messAdditional = '[br]'.$botSettings['SETTINGS']['DELIVERY_MESS'].'[br]';
			}
			
            $messCommand .= '[br][send=communication]Связаться с оператором[/send]';

            $mess = '[b]Каталог аксессуаров:[/b]';
            foreach ($resWater['result'] as $water) {
                $resBasket = restCommand('entity.item.get', Array(
                    'ENTITY' => BASKET_CODE,
                    'FILTER' => array(
                        'PROPERTY_BASKET_USER_ID' => $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID'],
                        'PROPERTY_BASKET_ITEM_ID' => $water['ID'],
                        'PROPERTY_BASKET_TYPE' => '3',
                    ),
                    'SORT' => array(
                        'DATE_ACTIVE_FROM' => 'ASC',
                        'ID' => 'ASC',
                    ),
                ), $_REQUEST['auth']);
                $mess .= $water['PREVIEW_PICTURE'].'[br]'.'[b]Выбрать:[/b] ';
                if ($resBasket['result']) {
                    $mess .= '[send=delete_basket_accessories '.$water['ID'].'](X)[/send] ';
                }
                $mess .= '[send=add_basket_accessories '.$water['ID'].'_1]1[/send] ';
                $mess .= '[send=add_basket_accessories '.$water['ID'].'_2]2[/send] ';
                $mess .= '[send=add_basket_accessories '.$water['ID'].'_3]3[/send] ';
                $mess .= '[send=add_basket_accessories '.$water['ID'].'_4]4[/send] ';
                $mess .= '[send=add_basket_accessories '.$water['ID'].'_5]5[/send] ';
                $mess .= '[send=add_basket_accessories '.$water['ID'].'_6]6[/send] ';
                $mess .= '[br]';
            }

        } else {

            $mess = 'Выберите аксессуар:[br][br]';

            $waterCount = 0;
            foreach ($resWater['result'] as $water) {
                $mess .= $water['NAME'].' ('.$water['PROPERTY_VALUES']['PRODUCT_PRICE'].' ₽)'.'[br]';
                $mess .= (count($resWater['result']) + 1 - $waterCount).'1: Купить 1 шт.'.'[br]';
                $mess .= (count($resWater['result']) + 1 - $waterCount).'2: Купить 2 шт.'.'[br]';
                $mess .= (count($resWater['result']) + 1 - $waterCount).'3: Купить 3 шт.'.'[br]';
                $mess .= (count($resWater['result']) + 1 - $waterCount).'4: Купить 4 шт.'.'[br]';
                $mess .= (count($resWater['result']) + 1 - $waterCount).'5: Купить 5 шт.'.'[br]';
                $mess .= (count($resWater['result']) + 1 - $waterCount).'6: Купить 6 шт.'.'[br]';
                $mess .= '[br]';
                $waterCount++;
            }
            
			if ($botSettings['SETTINGS']['DELIVERY_MESS']) {
				$messAdditional = $botSettings['SETTINGS']['DELIVERY_MESS'].'[br]';
			}

            $messCommand = '1: Указать адрес доставки[br]';
            $messCommand .= '0: Связаться с оператором[br]';

        }
        
		if ($messAdditional) {
			$mess .= $messAdditional;
		}
		
		$result = restCommand('imbot.message.add', Array(
			"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
			"MESSAGE" => $mess.'[br]'.$messCommand,
		), $_REQUEST["auth"]);
		
	}
	
}
