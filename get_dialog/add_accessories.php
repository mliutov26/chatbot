<?php

switch ($expectedOption) {
	case 'name':

		if($event == 'ONIMCOMMANDADD') {
			if($command['COMMAND'] !== 'exit_state' && $command['COMMAND'] !== 'reset_settings') {
				$result = restCommand('imbot.command.answer', Array(
					'COMMAND_ID' => $command['COMMAND_ID'],
					'MESSAGE_ID' => $messageId,
					'MESSAGE' => 'Бот находится в режиме добавления аксессуара.[br]Введите название товара или [send=/exit_state]выйдите из режима[/send]',
				), $arAuth);
				exit;
			}
		} else {
			$resAdd = restCommand('entity.item.add', Array(
				'ENTITY' => CAT_DETAILS,
				'NAME' => $message
			), $arAuth);

			$dialogSettings['SETTINGS']['ID'] = $resAdd['result'];
			$dialogSettings['SETTINGS']['PARAM'] = 'price';

			saveSettings(WBOT_CODE, $dialogId, $arAuth, $dialogSettings['SETTINGS']);

			$result = restCommand('imbot.message.add', Array(
				'DIALOG_ID' => $dialogId,
				'MESSAGE' => 'Введите цену товара',
			), $arAuth);
			exit;
		}
		break;
	case 'image':
		if($event == 'ONIMCOMMANDADD') {
			if($command['COMMAND'] !== 'exit_state' || $command['COMMAND'] !== 'reset_settings') {
				$result = restCommand('imbot.command.answer', Array(
					'COMMAND_ID' => $command['COMMAND_ID'],
					'MESSAGE_ID' => $messageId,
					'MESSAGE' => 'Бот находится в режиме добавления аксессуара.[br]Введите ссылку на изображение (окончание ссылки должно быть .png, .jpg, .gif) или [send=/exit_state]выйдите из режима[/send]',
				), $arAuth);
				exit;
			}
		} else {
			cnLog::Add('add_accessories getDialog image: '.print_r($message, true));
			$arrFile = $_REQUEST['data']['PARAMS']['FILES'];
			$attachFile = current($arrFile);

			cnLog::Add('add_accessories getDialog add_water image $attachFile: '.print_r($attachFile, true));

			saveProductImage($productId, $attachFile, $message, $dialogId, CAT_DETAILS, $arAuth);

			$res = restCommand('entity.item.get', Array(
				'ENTITY' => CAT_DETAILS,
				'FILTER' => array('ID' => $dialogSettings['SETTINGS']['ID'])
			), $arAuth);

			$element = current($res);
			cnLog::Add('add_accessories getDialog entity.item.get: '.print_r($element, true));
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
				'MESSAGE' => 'Товар добавлен в каталог аксессуаров. [br][send=/add_accessories]Добавить еще[/send] [br][send=/accessories]Посмотреть весь каталог аксессуаров[/send]',
				'KEYBOARD' => $keyboard,
			), $arAuth);
			cnLog::Add('add_accessories getDialog $newSettings price: '.print_r($newSettings, true));
			exit;
		}

		break;
	case 'price':
		if($event == 'ONIMCOMMANDADD') {
			if($command['COMMAND'] !== 'exit_state' || $command['COMMAND'] !== 'reset_settings') {
				$result = restCommand('imbot.command.answer', Array(
					'COMMAND_ID' => $command['COMMAND_ID'],
					'MESSAGE_ID' => $messageId,
					'MESSAGE' => 'Бот находится в режиме добавления товара.[br]Введите цену или [send=/exit_state]выйдите из режима[/send]',
				), $arAuth);
				exit;
			}
		} else {
			validateNumber($message, $dialogId, $arAuth);
			//cnLog::Add('add_accessories getDialog price: '.print_r($message, true));
			$resUpdate = restCommand('entity.item.update', Array(
				'ENTITY' => CAT_DETAILS,
				'ID' => $productId,
				'PROPERTY_VALUES' => array(
					'PRODUCT_PRICE' => $message,
					),
			), $arAuth);

			//cnLog::Add('add_accessories getDialog price: '.print_r($resUpdate, true));
			$dialogSettings['SETTINGS']['PARAM'] = 'image';
			saveSettings(WBOT_CODE, $dialogId, $arAuth, $dialogSettings['SETTINGS']);
			$result = restCommand('imbot.message.add', Array(
				'DIALOG_ID' => $dialogId,
				'MESSAGE' => 'Введите ссылку на изображение (окончание ссылки должно быть .png, .jpg, .gif)',
			), $arAuth);
			exit;
		}
		break;		
}
