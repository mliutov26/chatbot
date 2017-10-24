<?php

switch ($expectedOption) {
	
	case 'name':
		if($event == 'ONIMCOMMANDADD') {

			if($command['COMMAND'] !== 'exit_state' && $command['COMMAND'] !== 'reset_settings') {
				$result = restCommand('imbot.command.answer', Array(
					'COMMAND_ID' => $command['COMMAND_ID'],
					'MESSAGE_ID' => $messageId,
					'MESSAGE' => 'Бот находится в режиме добавления товара.[br]Введите название товара или [send=/exit_state]выйдите из режима[/send]',
				), $arAuth);
				exit;
			}

		} else {

			$resAdd = restCommand('entity.item.add', Array(
				'ENTITY' => CATALOG_CODE,
				'NAME' => $message
			), $arAuth);

			$dialogSettings['SETTINGS']['ID'] = $resAdd['result'];
			$dialogSettings['SETTINGS']['PARAM'] = 'volume';

			saveSettings(WBOT_CODE, $dialogId, $arAuth, $dialogSettings['SETTINGS']);

			//$newSettings = loadSettings(WBOT_CODE, $dialogId, $arAuth);
			// cnLog::Add('getDialog newSettings: '.print_r($newSettings, true));

			$result = restCommand('imbot.message.add', Array(
				'DIALOG_ID' => $dialogId,
				'MESSAGE' => 'Введите объем товара в литрах',
			), $arAuth);	
			exit;
		}
		break;

	case 'image':
		if($event == 'ONIMCOMMANDADD') {
			if($command['COMMAND'] !== 'exit_state' && $command['COMMAND'] !== 'reset_settings') {
				$result = restCommand('imbot.command.answer', Array(
					'COMMAND_ID' => $command['COMMAND_ID'],
					'MESSAGE_ID' => $messageId,
					'MESSAGE' => 'Бот находится в режиме добавления товара.[br]Введите ссылку на изображение (окончание ссылки должно быть .png, .jpg, .gif) или [send=/exit_state]выйдите из режима[/send]',
				), $arAuth);
				exit;
			}
		} else {
			
			$arrFile = $_REQUEST['data']['PARAMS']['FILES'];
			$attachFile = current($arrFile);
			
			saveProductImage($productId, $attachFile, $message, $dialogId, CATALOG_CODE, $arAuth);

			$res = restCommand('entity.item.get', Array(
				'ENTITY' => CATALOG_CODE,
				'FILTER' => array('ID' => $dialogSettings['SETTINGS']['ID'])
			), $arAuth);

			$element = current($res);
			// cnLog::Add('Товар добавлен entity.item.get: '.print_r($element, true));
			// cnLog::Add('getDialog entity.item.add ID: '.print_r($resAdd['result'], true));
			$dialogSettings['SETTINGS'] = array(
					'STATE' => '',
					'PARAM' => '',
					'ID' => '',
				);
			saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);


			$keyboard = Array(
                Array('TYPE' => 'NEWLINE'),
                Array(
                    'TEXT' => 'Доступные команды',
                    'BG_COLOR' => '#e8e9eb',
                    'TEXT_COLOR' => '#333',
                    'DISPLAY' => 'LINE',
                    'COMMAND' => 'settings',
                ),
				/*Array(
					'TEXT' => 'Доступные команды',
					'BG_COLOR' => '#e8e9eb',
					'TEXT_COLOR' => '#333',
					'DISPLAY' => 'LINE',
					'COMMAND' => 'available_commands',
					'COMMAND_PARAMS' => 'water accessories delivery discount refund fine hello',
				),
				Array(
					'TEXT' => 'Сбросить все настройки',
					'BG_COLOR' => '#aeb1b7',
					'TEXT_COLOR' => '#f7f6f6',
					'DISPLAY' => 'LINE',
					'COMMAND' => 'reset_settings',
				),*/
			);

			$result = restCommand('imbot.message.add', Array(
				'DIALOG_ID' => $dialogId,
				'MESSAGE' => 'Товар добавлен в каталог воды. [br][send=/add_water]Добавить ещё[/send][br][send=/water]Посмотреть весь каталог воды[/send]',
				'KEYBOARD' => $keyboard,
			), $arAuth);


			// $dialogSettings['SETTINGS']['PARAM'] = 'volume';
			// saveSettings(WBOT_CODE, $dialogId, $arAuth, $dialogSettings['SETTINGS']);

			// $result = restCommand('imbot.message.add', Array(
			// 	'DIALOG_ID' => $dialogId,
			// 	'MESSAGE' => 'Введите объем товара',
			// ), $arAuth);				
			//cnLog::Add('getDialog $dialogSettings image: '.print_r($dialogSettings, true));
			exit;
		}
		break;
		
	case 'volume':
		if($event == 'ONIMCOMMANDADD') {
			if($command['COMMAND'] !== 'exit_state' && $command['COMMAND'] !== 'reset_settings') {
				$result = restCommand('imbot.command.answer', Array(
					'COMMAND_ID' => $command['COMMAND_ID'],
					'MESSAGE_ID' => $messageId,
					'MESSAGE' => 'Бот находится в режиме добавления товара.[br]Введите объем товара в литрах или [send=/exit_state]выйдите из режима[/send]',
				), $arAuth);
				exit;
			}
		} else {
			cnLog::Add('getDialog volume: '.print_r($message, true));
			validateNumber($message, $dialogId, $arAuth);
			$resUpdate = restCommand('entity.item.update', Array(
				'ENTITY' => CATALOG_CODE,
				'ID' => $productId,
				'PROPERTY_VALUES' => array(
					'PRODUCT_VOLUME' => $message,
					),
			), $arAuth);

			cnLog::Add('getDialog volume: '.print_r($resUpdate, true));
			// cnLog::Add('getDialog entity.item.add ID: '.print_r($resAdd['result'], true));

			if(array_key_exists('error', $resUpdate) && !empty($resUpdate['error'])) {
				$result = restCommand('imbot.message.add', Array(
					'DIALOG_ID' => $dialogId,
					'MESSAGE' => 'Что-то пошло не так, попробуйте еще раз или [send=/exit_state]выйдите из режима[/send]',
				), $arAuth);
				exit;
			}

			$dialogSettings['SETTINGS']['PARAM'] = 'price';
			saveSettings(WBOT_CODE, $dialogId, $arAuth, $dialogSettings['SETTINGS']);

			$result = restCommand('imbot.message.add', Array(
				'DIALOG_ID' => $dialogId,
				'MESSAGE' => 'Введите цену товара в рублях',
			), $arAuth);
			//cnLog::Add('getDialog $dialogSettings volume: '.print_r($dialogSettings, true));
			exit;
		}
		break;
		
	case 'price':
		if($event == 'ONIMCOMMANDADD') {
			if($command['COMMAND'] !== 'exit_state' && $command['COMMAND'] !== 'reset_settings') {
				$result = restCommand('imbot.command.answer', Array(
					'COMMAND_ID' => $command['COMMAND_ID'],
					'MESSAGE_ID' => $messageId,
					'MESSAGE' => 'Бот находится в режиме добавления товара.[br]Введите цену в рублях или [send=/exit_state]выйдите из режима[/send]',
				), $arAuth);
				exit;
			}
		} else {
			cnLog::Add('getDialog price: '.print_r($message, true));
			validateNumber($message, $dialogId, $arAuth);
			$resUpdate = restCommand('entity.item.update', Array(
				'ENTITY' => CATALOG_CODE,
				'ID' => $productId,
				'PROPERTY_VALUES' => array(
					'PRODUCT_PRICE' => $message,
					),
			), $arAuth);

			cnLog::Add('getDialog volume: '.print_r($resUpdate, true));

			if(array_key_exists('error', $resUpdate) && !empty($resUpdate['error'])) {
				$result = restCommand('imbot.message.add', Array(
					'DIALOG_ID' => $dialogId,
					'MESSAGE' => 'Что-то пошло не так, попробуйте еще раз или [send=/exit_state]выйдите из режима[/send]',
				), $arAuth);
				exit;
			}

			$dialogSettings['SETTINGS']['PARAM'] = 'image';
			saveSettings(WBOT_CODE, $dialogId, $arAuth, $dialogSettings['SETTINGS']);

			$result = restCommand('imbot.message.add', Array(
				'DIALOG_ID' => $dialogId,
				'MESSAGE' => 'Введите ссылку на изображение (окончание ссылки должно быть .png, .jpg, .gif)',
			), $arAuth);

			exit;
		}
		break;	
		
	default:
		break;
				
}
