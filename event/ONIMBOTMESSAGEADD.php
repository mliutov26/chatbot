<?php

define('MESADDEVENT_PATH', ROOT_PATH.'/event/onImMessageAdd');


// ветка для открытых линий
if ($_REQUEST['data']['PARAMS']['CHAT_ENTITY_TYPE'] == 'LINES') {
	
	$chatType = explode('|', $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID']);
	
	// ветка для открытых линий (б24 и livechat)
	if ($chatType['0'] == 'livechat' || $chatType['0'] == 'network') {
	
		if ($botSettings['SETTINGS']['ADMIN_CRM']) {
			
			if ($_REQUEST['data']['PARAMS']['MESSAGE']) {

				$commandString = stristr($_REQUEST['data']['PARAMS']['MESSAGE'].' ', ' ', true);
				$messageString = trim(stristr($_REQUEST['data']['PARAMS']['MESSAGE'], ' '));
				$messageArray = explode('_', $messageString);
				
				if ($commandString && file_exists(MESADDEVENT_PATH.'/'.$commandString.'.php')) {
					require_once(MESADDEVENT_PATH.'/'.$commandString.'.php');
				} else {

				    if ($botSettings['SETTINGS']['HELLO_MESS']) {
                        $mess = $botSettings['SETTINGS']['HELLO_MESS'].'[br][br]'.
                            '[send=new_order]Оформить заказ[/send][br]';
                    } else {
                        $mess = 'Здравствуйте, вы можете:[br][br]'.
                            '[send=new_order]Оформить заказ[/send][br]';
                    }

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
						$mess .= '[send=repeat_order]Повторить последний заказ[/send][br]';
					}
					
					$mess .= '[send=communication]Связаться с оператором[/send]';
					$result = restCommand('imbot.message.add', Array(
						"DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
						"MESSAGE" => $mess,
					), $_REQUEST["auth"]);
				}

			}
			
		} else {
			
			$result = restCommand('imbot.message.add', Array(
				'DIALOG_ID' => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
				'MESSAGE' => 'Извините, сервис временно недоступен',
			), $_REQUEST['auth']);
			
		}
		
	// ветка для открытых линий (соц.сети)
	} else {
		
		$stepArray = Array(
			'step1' => Array(
				'0' => 'communication',
				'1' => 'new_order',
				'2' => 'repeat_order',
				'next_step_1' => 'step2',
                'next_step_2' => 'step12',
            ),
			'step2' => Array(
				'0' => 'communication',
            ),
            'step3' => Array(
                '0' => 'communication',
                '1' => 'clear_basket',
                '2' => 'new_order',
                'next_step_1' => 'step1',
                'next_step_2' => 'step2',
            ),
            'step4' => Array(
                '0' => 'communication',
                '11' => 'add_basket_refund|1',
                '12' => 'add_basket_refund|2',
                '13' => 'add_basket_refund|3',
                '14' => 'add_basket_refund|4',
                '15' => 'add_basket_refund|5',
                '16' => 'add_basket_refund|6',
                'next_step_11' => 'step5',
                'next_step_12' => 'step5',
                'next_step_13' => 'step5',
                'next_step_14' => 'step5',
                'next_step_15' => 'step5',
                'next_step_16' => 'step5',
            ),
            'step5' => Array(
                '0' => 'communication',
                '1' => 'clear_basket',
                '2' => 'new_order',
                '3' => 'new_order_refund',
                'next_step_1' => 'step1',
                'next_step_2' => 'step2',
                'next_step_3' => 'step4',
            ),
            'step6' => Array(
                '0' => 'communication',
                '1' => 'add_address',
                'next_step_1' => 'step9',
            ),
            'step7' => Array(
                '0' => 'communication',
                '1' => 'clear_basket',
                '2' => 'new_order',
                '3' => 'new_order_accessories',
                '4' => 'add_address',
                'next_step_1' => 'step1',
                'next_step_2' => 'step2',
                'next_step_3' => 'step6',
                'next_step_4' => 'step9',
            ),
            'step8' => Array(
                '0' => 'communication',
                '1' => 'new_order',
                '2' => 'add_address',
                'next_step_1' => 'step2',
                'next_step_2' => 'step9',
            ),
            'step9' => Array(
                '0' => 'communication',
                '1' => 'new_order',
                '2' => 'add_address|new_address',
                '3' => 'add_phone',
                'next_step_1' => 'step2',
                'next_step_2' => 'step9',
                'next_step_3' => 'step10',
            ),
            'step10' => Array(
                '0' => 'communication',
                '1' => 'new_order',
                '2' => 'add_phone|new_phone',
                '3' => 'add_address',
                '4' => 'new_order_fin|fin_order',
                'next_step_1' => 'step2',
                'next_step_2' => 'step10',
                'next_step_3' => 'step9',
                'next_step_4' => 'step11',
            ),
            'step11' => Array(
                '0' => 'communication',
                '1' => 'new_order',
                '2' => 'add_address',
                '3' => 'add_phone',
                '4' => 'new_order_fin|fin_order',
                'next_step_1' => 'step2',
                'next_step_2' => 'step9',
                'next_step_3' => 'step10',
                'next_step_4' => 'step11',
            ),
            'step12' => Array(
                '0' => 'communication',
                '1' => 'new_order',
                '2' => 'new_order_fin',
                'next_step_1' => 'step2',
                'next_step_2' => 'step11',
            ),
            
        ); 

		if ($dialogSettings['SETTINGS']['PARAM'] == 'step2') {
            $resWater = restCommand('entity.item.get', Array(
                'ENTITY' => CATALOG_CODE,
                'SORT' => array(
                    'DATE_ACTIVE_FROM' => 'ASC',
                    'ID' => 'ASC',
                ),
            ), $_REQUEST['auth']);
            $waterCounter = count($resWater['result']) + 1;
            foreach ($resWater['result'] as $water) {
                for ($i = 1; $i <= 6; $i++) {
                    $stepArray['step2'][$waterCounter . $i] = 'add_basket|' . $water['ID'] . '_' . $i;
                    $stepArray['step2']['next_step_' . $waterCounter . $i] = 'step3';
                }
                $waterCounter--;
            }
            if ($botSettings['SETTINGS']['REFUND_MESS']) {
                $stepArray['step2']['1'] = 'new_order_refund';
                $stepArray['step2']['next_step_1'] = 'step4';
            } elseif (!empty($resAccessories['result'])) {
                $stepArray['step2']['1'] = 'new_order_accessories';
                $stepArray['step2']['next_step_1'] = 'step6';
            } else {
                $stepArray['step2']['1'] = 'add_address';
                $stepArray['step2']['next_step_1'] = 'step9';
            }
        }

        if ($dialogSettings['SETTINGS']['PARAM'] == 'step3') {
            $resAccessories = restCommand('entity.item.get', Array(
                'ENTITY' => CAT_DETAILS,
                'SORT' => array(
                    'DATE_ACTIVE_FROM' => 'ASC',
                    'ID' => 'ASC',
                ),
            ), $_REQUEST['auth']);
            if ($botSettings['SETTINGS']['REFUND_MESS']) {
                $stepArray['step3']['3'] = 'new_order_refund';
                $stepArray['step3']['next_step_3'] = 'step4';
            } elseif (!empty($resAccessories['result'])) {
                $stepArray['step3']['3'] = 'new_order_accessories';
                $stepArray['step3']['next_step_3'] = 'step6';
            } else {
                $stepArray['step3']['3'] = 'add_address';
                $stepArray['step3']['next_step_3'] = 'step9';
            }
        }

        if ($dialogSettings['SETTINGS']['PARAM'] == 'step4') {
            $resAccessories = restCommand('entity.item.get', Array(
                'ENTITY' => CAT_DETAILS,
                'SORT' => array(
                    'DATE_ACTIVE_FROM' => 'ASC',
                    'ID' => 'ASC',
                ),
            ), $_REQUEST['auth']);
            if (!empty($resAccessories['result'])) {
                $stepArray['step4']['1'] = 'new_order_accessories';
                $stepArray['step4']['next_step_1'] = 'step6';
            } else {
                $stepArray['step4']['1'] = 'add_address';
                $stepArray['step4']['next_step_1'] = 'step9';
            }
        }

        if ($dialogSettings['SETTINGS']['PARAM'] == 'step5') {
            $resAccessories = restCommand('entity.item.get', Array(
                'ENTITY' => CAT_DETAILS,
                'SORT' => array(
                    'DATE_ACTIVE_FROM' => 'ASC',
                    'ID' => 'ASC',
                ),
            ), $_REQUEST['auth']);
            if (!empty($resAccessories['result'])) {
                $stepArray['step5']['4'] = 'new_order_accessories';
                $stepArray['step5']['next_step_4'] = 'step6';
            } else {
                $stepArray['step5']['4'] = 'add_address';
                $stepArray['step5']['next_step_4'] = 'step9';
            }
        }

        if ($dialogSettings['SETTINGS']['PARAM'] == 'step6') {
            $resWater = restCommand('entity.item.get', Array(
                'ENTITY' => CAT_DETAILS,
                'SORT' => array(
                    'DATE_ACTIVE_FROM' => 'ASC',
                    'ID' => 'ASC',
                ),
            ), $_REQUEST['auth']);
            $waterCounter = count($resWater['result']) + 1;
            foreach ($resWater['result'] as $water) {
                for ($i = 1; $i <= 6; $i++) {
                    $stepArray['step6'][$waterCounter.$i] = 'add_basket_accessories|'.$water['ID'].'_'.$i;
                    $stepArray['step6']['next_step_'.$waterCounter.$i] = 'step7';
                }
                $waterCounter--;
            }
        }
       
        if ($dialogSettings['SETTINGS']['PARAM'] == 'step9' && !$stepArray[$dialogSettings['SETTINGS']['PARAM']][$_REQUEST['data']['PARAMS']['MESSAGE']]) {
		
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
			
			$thisAdress = $resOrderN['result']['0']['PROPERTY_VALUES']['ORDER_ADRESS'];

            if ($resOrder['result']) {
                $addressArray = Array();
                $address = Array();
                foreach ($resOrder['result'] as $order) {
                	if ($order['PROPERTY_VALUES']['ORDER_ADRESS'] != $thisAdress) {
                    	$addressArray[] = $order['PROPERTY_VALUES']['ORDER_ADRESS'];
                    }
                }
                $addressArray = array_unique($addressArray);
                $adressCounter = count($addressArray) + 3;
                if ($thisAdress) {
               		$adressCounter++;
               	}
                foreach ($addressArray as $addressArrayItem) {
                    $address[$adressCounter] = $addressArrayItem;
                    $adressCounter--;
                }
            }
            
            if ($thisAdress) {
            	$address['4'] = $thisAdress;
            }

            if ($address[$_REQUEST['data']['PARAMS']['MESSAGE']]) {
                $dialogSettings['SETTINGS']['PARAM_COMMAND'] = $address[$_REQUEST['data']['PARAMS']['MESSAGE']];
            } else {
                $dialogSettings['SETTINGS']['PARAM_COMMAND'] = $_REQUEST['data']['PARAMS']['MESSAGE'];
            }
            $dialogSettings['SETTINGS']['PARAM'] = 'step9';
            require_once(MESADDEVENT_PATH.'/add_address.php');
            saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

        }

        if ($dialogSettings['SETTINGS']['PARAM'] == 'step10' && !$stepArray[$dialogSettings['SETTINGS']['PARAM']][$_REQUEST['data']['PARAMS']['MESSAGE']]) {

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

            if ($resOrder['result']) {
                $phoneArray = Array();
                $phone = Array();
                foreach ($resOrder['result'] as $order) {
                	if ($order['PROPERTY_VALUES']['ORDER_PHONE'] != $thisPhone) {
                    	$phoneArray[] = $order['PROPERTY_VALUES']['ORDER_PHONE'];
                    }
                }
                $phoneArray = array_unique($phoneArray);
                $phoneCounter = count($phoneArray) + 4;
                if ($thisPhone) {
               		$phoneCounter++;
               	}
                foreach ($phoneArray as $phoneArrayItem) {
                    $phone[$phoneCounter] = $phoneArrayItem;
                    $phoneCounter--;
                }
            }
            
            if ($thisPhone) {
            	$phone['5'] = $thisPhone;
            }

            if ($phone[$_REQUEST['data']['PARAMS']['MESSAGE']]) {
                $dialogSettings['SETTINGS']['PARAM_COMMAND'] = $phone[$_REQUEST['data']['PARAMS']['MESSAGE']];
            } else {
                $dialogSettings['SETTINGS']['PARAM_COMMAND'] = $_REQUEST['data']['PARAMS']['MESSAGE'];
            }

            $dialogSettings['SETTINGS']['PARAM'] = 'step10';
            require_once(MESADDEVENT_PATH.'/add_phone.php');
            saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

        }

		if ($dialogSettings['SETTINGS']['STATE'] == 'step' && $stepArray[$dialogSettings['SETTINGS']['PARAM']][$_REQUEST['data']['PARAMS']['MESSAGE']]) {
		
			$commandString = explode('|', $stepArray[$dialogSettings['SETTINGS']['PARAM']][$_REQUEST['data']['PARAMS']['MESSAGE']]);
			if ($commandString[1]) {
				$dialogSettings['SETTINGS']['PARAM_COMMAND'] = $commandString[1];
			}
			
			if ($commandString[0] && file_exists(MESADDEVENT_PATH.'/'.$commandString[0].'.php')) {
				require_once(MESADDEVENT_PATH.'/'.$commandString[0].'.php');
			}
			
			if ($stepArray[$dialogSettings['SETTINGS']['PARAM']]['next_step_'.$_REQUEST['data']['PARAMS']['MESSAGE']]) {
				$dialogSettings['SETTINGS']['PARAM'] = $stepArray[$dialogSettings['SETTINGS']['PARAM']]['next_step_'.$_REQUEST['data']['PARAMS']['MESSAGE']];
            }

            saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

        } else {

		    if ($dialogSettings['SETTINGS']['PARAM'] != 'step9' && $dialogSettings['SETTINGS']['PARAM'] != 'step10') {

                if ($botSettings['SETTINGS']['HELLO_MESS']) {
                    $mess = $botSettings['SETTINGS']['HELLO_MESS'].'[br][br]';
                } else {
                    $mess = 'Здравствуйте, вы можете:[br][br]';
                }

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
                    $mess .= '2: Повторить последний заказ[br]';
                }
                
                $mess .= '1: Оформить заказ[br]';

                $mess .= '0: Связаться с оператором';
                $result = restCommand('imbot.message.add', Array(
                    "DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
                    "MESSAGE" => $mess,
                ), $_REQUEST["auth"]);

                $dialogSettings['SETTINGS']['STATE'] = "step";
                $dialogSettings['SETTINGS']['PARAM'] = "step1";
                saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

            }

		}
	
	}

// ветка для приложения
} else {
	
	if ($botSettings['SETTINGS']['ADMIN_CRM']) {
		
		$chatUserId = $_REQUEST['data']['USER']['ID'];
		$arrAdmins = $botSettings['SETTINGS']['ADMINS'];
	
		if(array_key_exists($chatUserId, $arrAdmins)) {
			
			$dialogId = $_REQUEST['data']['PARAMS']['DIALOG_ID'];
			$message = $_REQUEST['data']['PARAMS']['MESSAGE'];
			$messageId = $_REQUEST['data']['PARAMS']['MESSAGE_ID'];
			getDialog($dialogId, $message, $_REQUEST['event'], $messageId, $_REQUEST['auth']);
			
		} else {
			$result = restCommand('imbot.message.add', Array(
				'DIALOG_ID' => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
				'MESSAGE' => 'Ошибка запроса. Проверьте корректность ввода команды',
			), $_REQUEST['auth']);
		}
	
	} else {
			
		$result = restCommand('imbot.message.add', Array(
			'DIALOG_ID' => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
			'MESSAGE' => 'Переустановите бота под пользователем с доступом в CRM',
		), $_REQUEST['auth']);
			
	}

}
