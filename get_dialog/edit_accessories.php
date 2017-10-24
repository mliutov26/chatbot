<?php

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
		'COMMAND_PARAMS' => 'add_water water accessories delivery discount refund fine admins hello',
	),
	Array(
		'TEXT' => 'Сбросить все настройки',
		'BG_COLOR' => '#aeb1b7',
		'TEXT_COLOR' => '#f7f6f6',
		'DISPLAY' => 'LINE',
		'COMMAND' => 'reset_settings',
	),*/
);

if(!$productId) {
	$resultEditWater = restCommand('imbot.command.answer', Array(
		'COMMAND_ID' => $command['COMMAND_ID'],
		'MESSAGE_ID' => $messageId,
		'MESSAGE' => 'Ой, что-то пошло не так :( Не найден ID товара',
		'KEYBOARD' => $keyboard,
	), $arAuth);
	exit;
}

$resSettingsEditAccessory = restCommand('entity.item.get', Array(
	'ENTITY' => CAT_DETAILS,
	'FILTER' => array('ID' => $productId),
), $arAuth);

$curAccessory = current($resSettingsEditAccessory['result']);

switch ($expectedOption) {
	case 'none':

		$mess = 'Бот находится в режиме редактирования аксессуара.[br] Выберите какой параметр редактировать: ';
		$mess .= '[put=/edit_accessories_name '.$curAccessory['NAME'].']название[/put], ';
		$mess .= '[send=/edit_accessories_image]изображение[/send], ';
		$mess .= '[put=/edit_accessories_price '.$curAccessory['PROPERTY_VALUES']['PRODUCT_PRICE'].']цена[/put]';
		$mess .= '[br]или [send=/exit_state]выйдите из режима[/send]';

		if($event == 'ONIMCOMMANDADD'){
			$arrAllowedCommand = array('edit_accessories_name', 'edit_accessories_image', 'edit_accessories_price', 'exit_state', 'reset_settings');
			if(!in_array($command['COMMAND'], $arrAllowedCommand)) {
				$result = restCommand('imbot.command.answer', Array(
					'COMMAND_ID' => $command['COMMAND_ID'],
					'MESSAGE_ID' => $messageId,
					'MESSAGE' => $mess,
					'KEYBOARD' => $keyboard,
				), $arAuth);
				exit;

			}
		} else {
			//cnLog::Add('edit_water $command messageadd: '.print_r($command['COMMAND'], true));
			$resultEditWater = restCommand('imbot.message.add', Array(
				'DIALOG_ID' => $dialogId,
				'MESSAGE' => $mess,
				'KEYBOARD' => $keyboard,
			), $arAuth);
			exit;
		}
		break;
	
	case 'image':
		if($event == 'ONIMCOMMANDADD'){
			if($command['COMMAND'] !== 'exit_state' && $command['COMMAND'] !== 'reset_settings') {
				$resultEditWater = restCommand('imbot.command.answer', Array(
					'COMMAND_ID' => $command['COMMAND_ID'],
					'MESSAGE_ID' => $messageId,
					'MESSAGE' => 'Бот находится в режиме редактирования изображения аксессуара.[br]Введите ссылку на изображение (окончание ссылки должно быть .png, .jpg, .gif) или [send=/exit_state]выйдите из режима[/send] ',
				), $arAuth);
				exit;
			}
		} else {
			//cnLog::Add('edit_water image: '.print_r($message, true));
			$arrFile = $_REQUEST['data']['PARAMS']['FILES'];
			$attachFile = current($arrFile);

			//cnLog::Add('edit_water image: '.print_r($message, true));
			$arrFile = $_REQUEST['data']['PARAMS']['FILES'];
			$attachFile = current($arrFile);
			//$message =  $_REQUEST['data']['PARAMS']['MESSAGE'];

			if(!empty($attachFile)) {
				$editAccessoryMess = restCommand('imbot.message.add', Array(
					'COMMAND_ID' => $command['COMMAND_ID'],
					'MESSAGE' => 'Введите ссылку на изображение (окончание ссылки должно быть .png, .jpg, .gif)',
					'KEYBOARD' => $keyboard,
				), $arAuth);
				exit;
			} 

			if (preg_match('/\[URL=([^\]]+)\]/', $message, $matches)) {

				validateLink($matches[1], $dialogId, $arAuth);

				$curAccessoryId = $dialogSettings['SETTINGS']['ID'];
				$newImg = $matches[1];

				$resItem = restCommand('entity.item.get', Array(
					'ENTITY' => CAT_DETAILS,
					'FILTER' => array(
						'ID' => $curAccessoryId,
					),
				), $arAuth);


				if (array_key_exists('error', $resItem) && !empty($resItem['error'])) {
					$editAccessoryMess = restCommand('imbot.message.add', Array(
						'DIALOG_ID' => $dialogId,
						'MESSAGE' => 'Ой, что-то пошло не так. :( [br] Не удалось получить данные о товаре. [send=/exit_state]Завершить редактирование[/send]',
					), $arAuth);
					exit;
				}

				$item = current($resItem['result']);
				$itemName = $item['NAME'];
				$itemPrice = $item['PROPERTY_VALUES']['PRODUCT_PRICE'];

				// cnLog::Add('edit water name $itemName: '.print_r($itemName, true));
				// cnLog::Add('edit water name $itemPrice: '.print_r($itemPrice, true));
				// cnLog::Add('edit water name $itemVolume: '.print_r($itemVolume, true));

				imageGeneration(
					$newImg,
					ROOT_PATH.'/outputPhoto.jpg', // output photo file
					ROOT_PATH.'/font/DroidSansMono.ttf', // ttf font file 1
					ROOT_PATH.'/font/PTSans.ttf', // ttf font file 2
					$itemName, // item name
					$itemPrice, // item price
					$itemVolume, // item V
					'#000' // font color
				);

				$urlImg = base64_encode(file_get_contents(ROOT_PATH.'/outputPhoto.jpg'));
				$newUrlImg = base64_encode(file_get_contents($newImg));
				$nameImg = urldecode(basename($newImg));

				// cnLog::Add('edit water image $urlImg: '.print_r($urlImg, true));
				// cnLog::Add('edit water image $nameImg: '.print_r($nameImg , true));
				// cnLog::Add('edit water image $curWaterId: '.print_r($curWaterId , true));

				$resEditImage = restCommand('entity.item.update', Array(
					'ENTITY' => CAT_DETAILS,
					'ID' => $curAccessoryId,
					'PREVIEW_PICTURE' => array(
						$nameImg, $urlImg
					),
					'DETAIL_PICTURE' => array(
						$nameImg, $newUrlImg
					),
				), $arAuth);

				if (array_key_exists('error', $resEditImage) && !empty($resEditImage['error'])) {
					//cnLog::Add('edit water error $resEditImage: '.print_r($resEditImage , true));
					$editAccessoryMess = restCommand('imbot.message.add', Array(
						'DIALOG_ID' => $dialogId,
						'MESSAGE' => 'Ой, что-то пошло не так. :( [br] Не удалось обновить изображение товара. [send=/exit_state]Завершить редактирование[/send]',
					), $_REQUEST['auth']);

				} else {
					//cnLog::Add('edit water name $resEditImage: '.print_r($resEditImage , true));
					$dialogSettings['SETTINGS'] = array(
							'STATE' => '',
							'PARAM' => '',
							'ID' => '',
						);
					saveSettings(WBOT_CODE, $dialogId, $arAuth, $dialogSettings['SETTINGS']);
					$editAccessoryMess = restCommand('imbot.message.add', Array(
						'DIALOG_ID' => $dialogId,
						'MESSAGE' => 'Изображение отредактировано. [send=/accessories]Каталог аксессуаров[/send]',
						'KEYBOARD' => $keyboard,
					), $arAuth);
					exit;
				}
			} else {
				$editAccessoryMess = restCommand('imbot.message.add', Array(
					'DIALOG_ID' => $dialogId,
					'MESSAGE' => 'Нет ссылки на изображение. Попробуйте еще раз или [send=/exit_state]выйдите из режима[/send]',
				), $arAuth);
				exit;
			}

			// saveProductImage($productId, $attachFile, $message, $dialogId, CAT_DETAILS, $arAuth);
			// //cnLog::Add('edit image');
			// $dialogSettings['SETTINGS'] = array(
			// 		'STATE' => '',
			// 		'PARAM' => '',
			// 		'ID' => '',
			// 	);
			// saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);
			// $resultEditWater = restCommand('imbot.message.add', Array(
			// 	'DIALOG_ID' => $dialogId,
			// 	'MESSAGE' => 'Изображение отредактировано!',
			// 	'KEYBOARD' => $keyboard,
			// ), $arAuth);
			// exit;

		}
		break;
}
