<?php

cnLog::Add('edit water start');

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
		'COMMAND_PARAMS' => 'add_water water accessories delivery discount refund fine admins hello ',
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
		'MESSAGE' => 'Ой, что-то пошло не так :( Не найден ID товара [send=exit_state]Завершить редактирование[/send]',
		'KEYBOARD' => $keyboard,
	), $arAuth);
	exit;
}

$resSettingsEditWater = restCommand('entity.item.get', Array(
	'ENTITY' => CATALOG_CODE,
	'FILTER' => array('ID' => $productId),
), $arAuth);

$curWaterItem = current($resSettingsEditWater['result']);
cnLog::Add('edit_water $curWaterItem: '.print_r($curWaterItem, true));
cnLog::Add('edit_water $expectedOption: '.print_r($expectedOption, true));

switch ($expectedOption) {
	case 'none':

		$mess = 'Бот находится в режиме редактирования товара.[br] Выберите какой параметр редактировать: ';
		$mess .= '[put=/edit_water_name '.$curWaterItem['NAME'].']название[/put], ';
		$mess .= '[send=/edit_water_image]изображение[/send], ';
		$mess .= '[put=/edit_water_volume '.$curWaterItem['PROPERTY_VALUES']['PRODUCT_VOLUME'].']объем[/put], ';
		$mess .= '[put=/edit_water_price '.$curWaterItem['PROPERTY_VALUES']['PRODUCT_PRICE'].']цена[/put]';
		$mess .= '[br]или [send=/exit_state]выйдите из режима[/send]';

		if($event == 'ONIMCOMMANDADD'){
			$arrAllowedCommand = array('edit_water_name', 'edit_water_image', 'edit_water_volume', 'edit_water_price', 'exit_state', 'reset_settings');
			if(!in_array($command['COMMAND'], $arrAllowedCommand)) {
				//cnLog::Add('edit_water $command: '.print_r($command['COMMAND'], true));
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
					'MESSAGE' => 'Бот находится в режиме редактирования изображения.[br]Введите ссылку на изображение (окончание ссылки должно быть .png, .jpg, .gif) или [send=/exit_state]выйдите из режима[/send] ',
				), $arAuth);
				exit;
			}
		} else {
			
			cnLog::Add('edit_water image: '.print_r($message, true));
			
			$arrFile = $_REQUEST['data']['PARAMS']['FILES'];
			$attachFile = current($arrFile);
			//$message =  $_REQUEST['data']['PARAMS']['MESSAGE'];

			if(!empty($attachFile)) {
				$editWaterMess = restCommand('imbot.message.add', Array(
					'COMMAND_ID' => $command['COMMAND_ID'],
					'MESSAGE' => 'Введите ссылку на изображение (окончание ссылки должно быть .png, .jpg, .gif)',
					'KEYBOARD' => $keyboard,
				), $arAuth);
				exit;
			} 

			if (preg_match('/\[URL=([^\]]+)\]/', $message, $matches)) {

				validateLink($matches[1], $dialogId, $arAuth);

				$curWaterId = $dialogSettings['SETTINGS']['ID'];
				$newImg = $matches[1];

				$resItem = restCommand('entity.item.get', Array(
					'ENTITY' => CATALOG_CODE,
					'FILTER' => array(
						'ID' => $curWaterId,
					),
				), $arAuth);

				cnLog::Add('edit water name $resItem: '.print_r($resItem, true));

				if (array_key_exists('error', $resItem) && !empty($resItem['error'])) {
					$editWaterMess = restCommand('imbot.message.add', Array(
						'DIALOG_ID' => $dialogId,
						'MESSAGE' => 'Ой, что-то пошло не так. :( [br] Не удалось получить данные о товаре. [send=/exit_state]Завершить редактирование[/send]',
					), $arAuth);
					exit;
				}

				$item = current($resItem['result']);
				$itemName = $item['NAME'];
				$itemPrice = $item['PROPERTY_VALUES']['PRODUCT_PRICE'];
				$itemVolume = $item['PROPERTY_VALUES']['PRODUCT_VOLUME'];


				cnLog::Add('edit water name $itemName: '.print_r($itemName, true));
				cnLog::Add('edit water name $itemPrice: '.print_r($itemPrice, true));
				cnLog::Add('edit water name $itemVolume: '.print_r($itemVolume, true));

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
					'ENTITY' => CATALOG_CODE,
					'ID' => $curWaterId,
					'PREVIEW_PICTURE' => array(
						$nameImg, $urlImg
					),
					'DETAIL_PICTURE' => array(
						$nameImg, $newUrlImg
					),
				), $arAuth);

				if (array_key_exists('error', $resEditImage) && !empty($resEditImage['error'])) {
					//cnLog::Add('edit water error $resEditImage: '.print_r($resEditImage , true));
					$editWaterMess = restCommand('imbot.message.add', Array(
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
					saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);
					$resultEditWater = restCommand('imbot.message.add', Array(
						'DIALOG_ID' => $dialogId,
						'MESSAGE' => 'Изображение отредактировано. [send=/water]Каталог воды[/send]',
						'KEYBOARD' => $keyboard,
					), $arAuth);
					exit;
				}
			} else {
				$resultEditWater = restCommand('imbot.message.add', Array(
					'DIALOG_ID' => $dialogId,
					'MESSAGE' => 'Нет ссылки на изображение. Попробуйте еще раз или [send=/exit_state]выйдите из режима[/send]',
				), $arAuth);
				exit;
			}
			//saveProductImage($productId, $attachFile, $message, $dialogId, CATALOG_CODE, $arAuth);
			//cnLog::Add('edit image');
		}
		break;
	default:
		break;
}
