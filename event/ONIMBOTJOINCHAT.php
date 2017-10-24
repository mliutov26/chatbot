<?php

// check the event - authorize this event or not
/*if (!isset($appsConfig[$_REQUEST['auth']['application_token']]))
	return false;*/

if ($_REQUEST['data']['PARAMS']['CHAT_ENTITY_TYPE'] != 'LINES') {
	
	$chatUserId = $_REQUEST['data']['USER']['ID'];
	$chatUserName = $_REQUEST['data']['USER']['NAME'];
	$arrAdmins = $botSettings['SETTINGS']['ADMINS'];
	
	$resAdmin = restCommand('user.admin', array(), $_REQUEST['auth']);
	
	if (!$resAdmin['result'] && !array_key_exists($chatUserId, $arrAdmins)) {
	
		$resCatWater = restCommand('entity.item.get', Array(
			'ENTITY' => CATALOG_CODE,
			'SORT' => array(
				'DATE_ACTIVE_FROM' => 'ASC',
				'ID' => 'ASC',
			),
		), $_REQUEST['auth']);
	
		if($resCatWater['result']) {
			$result = restCommand('imbot.message.add', Array(
				'DIALOG_ID' => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
				'MESSAGE' => 'Добро пожаловать в чат. Здесь вы можете выбрать и заказать воду в дом или офис.',
			), $_REQUEST['auth']);
		} else {
			$result = restCommand('imbot.message.add', Array(
				'DIALOG_ID' => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
				'MESSAGE' => 'Извините, сервис временно недоступен :(',
			), $_REQUEST['auth']);
		}
	
	} else {
	
		$botSettings['SETTINGS']['ADMINS'][$chatUserId] = $chatUserName;
	
		if($chatUserId == $botSettings['SETTINGS']['CURR_ADMIN_ID'] || empty($botSettings['SETTINGS']['CURR_ADMIN_ID'])) {

			$botSettings['SETTINGS']['CURR_ADMIN_ID'] = $chatUserId;
			$botSettings['SETTINGS']['CURR_ADMIN_NAME'] = $chatUserName;
	
			$result = restCommand('imbot.message.add', Array(
				'DIALOG_ID' => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
				'MESSAGE' => 'Добро пожаловать в чат. :) [br] До начала работы необходимо настроить бота:[br] установить администраторов и добавить товары в каталог',
				'KEYBOARD' => Array(
					Array(
						'TEXT' => 'Установить администраторов',
						'BG_COLOR' => '#29619b',
						'TEXT_COLOR' => '#fff',
						'DISPLAY' => 'LINE',
						'COMMAND' => 'set_admins'
					),
				)
			), $_REQUEST['auth']);
			
		} else {

			$result = restCommand('imbot.message.add', Array(
				'DIALOG_ID' => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
				'MESSAGE' => 'Приложение редактирует админ: [b]'. $botSettings['SETTINGS']['CURR_ADMIN_NAME'] .'[/b]. [send=/change_cur_admin]Завершить редактирование[/send]',
			), $_REQUEST['auth']);

		}

        saveSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth'], $botSettings['SETTINGS']);
	
	}

}
