<?php

// handler for events

$handlerBackUrl = BACK_URL;

createRemoteSettings(ADMIN_SETTINGS, 'Настройки для админа', $arPropsAdmins, $_REQUEST['auth']);
createRemoteSettings(CATALOG_CODE, 'Каталог товаров', $arPropsCataloque, $_REQUEST['auth']);
createRemoteSettings(BASKET_CODE, 'Корзина товаров', $arPropsBasket, $_REQUEST['auth']);
createRemoteSettings(ORDER_CODE, 'Заказы', $arPropsOrder, $_REQUEST['auth']);
createRemoteSettings(WBOT_CODE, 'Статусы', $arPropsStatus, $_REQUEST['auth']);
createRemoteSettings(CAT_DETAILS, 'Аксессуары', $arPropsCataloque, $_REQUEST['auth']);

// If your application supports different localizations
// use $_REQUEST['data']['LANGUAGE_ID'] to load correct localization

$botSettings['SETTINGS']['ADMIN_CRM'] = '';
$leadPropArray = Array(
	'fields' => Array(
		'STATUS_ID' => 'NEW',
		'OPENED' => 'N', 
		'ASSIGNED_BY_ID' => 1,
		'TITLE' => 'Тестовый лид (Чат-бот)',
	)
);
$leadAdd = restCommand('crm.lead.add', $leadPropArray, $_REQUEST['auth']);
$thisUser = restCommand('user.get', Array('id' => $_REQUEST['auth']['user_id']), $_REQUEST['auth']);

if ($leadAdd['result']) {
	restCommand('crm.lead.delete', Array('id' => $leadAdd['result']), $_REQUEST['auth']);
	$mysqli = mysqli_connect(SQL_SERVER, SQL_LOGIN, SQL_PASSWORD, SQL_TABLE);
	if ($mysqli) {

        $botSettings['SETTINGS']['ADMIN_CRM'] = 'Y';
		$selectQuery = mysqli_query($mysqli, 'SELECT * FROM cn_mega_portal_reg WHERE APP_CODE = "waterbot" AND URL = "'.$_REQUEST['auth']['domain'].'"');
		if (mysqli_num_rows($selectQuery)) {
			$updateQuery = mysqli_query($mysqli, 'UPDATE cn_mega_portal_reg SET ACCESS_TOKEN = "'.$_REQUEST['auth']['access_token'].'", EXPIRES_IN = "'.$_REQUEST['auth']['expires_in'].'", REFRESH_TOKEN = "'.$_REQUEST['auth']['refresh_token'].'", MEMBER_ID = "'.$_REQUEST['auth']['member_id'].'", ADMIN_EMAIL = "'.$thisUser['result']['0']['EMAIL'].'", DATE_UPDATE = "'.date("Y-m-d H:i:s").'", EXT_MODIFIED_BY = "'.$_REQUEST['auth']['user_id'].'"  WHERE APP_CODE = "waterbot" AND URL = "'.$_REQUEST['auth']['domain'].'"');
			mysqli_close($updateQuery);		
		} else {
			$insertQuery = mysqli_query($mysqli, 'INSERT INTO cn_mega_portal_reg (APP_CODE, NAME, ACTIVE, URL, LANG_ID, ACCESS_TOKEN, EXPIRES_IN, REFRESH_TOKEN, MEMBER_ID, DATE_CREATE, DATE_UPDATE, EXT_MODIFIED_BY, EXT_CREATED_BY, ADMIN_EMAIL) VALUES ("waterbot", "'.$_REQUEST['auth']['domain'].'", 1, "'.$_REQUEST['auth']['domain'].'", "'.$_REQUEST['data']['LANGUAGE_ID'].'", "'.$_REQUEST['auth']['access_token'].'", "'.$_REQUEST['auth']['expires_in'].'", "'.$_REQUEST['auth']['refresh_token'].'", "'.$_REQUEST['auth']['member_id'].'", "'.date("Y-m-d H:i:s").'", "'.date("Y-m-d H:i:s").'", "'.$_REQUEST['auth']['user_id'].'", "'.$_REQUEST['auth']['user_id'].'", "'.$thisUser['result']['0']['EMAIL'].'")');
			mysqli_close($insertQuery);	
		}
		mysqli_close($selectQuery);	

		/*
		$userCurrent = restCommand('user.current', Array(), $_REQUEST['auth']);
		$appInfo = restCommand('app.info', Array(), $_REQUEST['auth']);
		$license = '';
		$licenseArray = explode('_', $appInfo['result']['LICENSE']);
		switch ($licenseArray[1]) {
			case 'project':
			$license = 'тариф Проект';
			break;
			case 'tf':
			$license = 'тариф Проект';
			break;
			case 'team':
			$license = 'тариф Команда';
			break;
			case 'company':
			$license = 'тариф Компания';
			break;
			case 'demo':
			$license = 'демо-режим';
			break;
			case 'nfr':
			$license = 'NFR-лицензия';
			break;
			case 'corportation':
			$license = 'Корпоративный тариф';
			break;
		}

		$arLead = Array(
			'TITLE' => 'Установка чат-бота Доставки воды',
			'UF_CRM_1485954527' => 'waterbot',
			'UF_CRM_1448279892' => $_REQUEST['auth']['domain'],
			'UF_CRM_1486638921' => $license,
			'NAME' => $userCurrent['result']['NAME'],
			'LAST_NAME' => $userCurrent['result']['LAST_NAME'],
			'SECOND_NAME' => $userCurrent['result']['SECOND_NAME'],
			'EMAIL' => Array(
				Array('VALUE' => $userCurrent['result']['EMAIL'], 'VALUE_TYPE'=>'WORK'),
			),
			'PHONE' => Array(
				Array('VALUE' => $userCurrent['result']['PERSONAL_PHONE'], 'VALUE_TYPE'=>'HOME'),
				Array('VALUE' => $userCurrent['result']['PERSONAL_MOBILE'], 'VALUE_TYPE'=>'MOBILE'),
				Array('VALUE' => $userCurrent['result']['WORK_PHONE'], 'VALUE_TYPE'=>'WORK'),
			),
			'STATUS_ID' => 'NEW',
			'OPENED' => 'Y',
			'ASSIGNED_BY_ID' => '1050',
		);

		// добавляем лид через вебхук
		require_once(LIB_PATH.'/cn_info24_lead.php');
		$res = \CnInfo24Lead::getList(
			array(),
			Array(
				'TITLE' => 'Установка чат-бота Доставки воды',
				'UF_CRM_1485954527' => 'waterbot',
				'UF_CRM_1448279892' => $_REQUEST['auth']['domain'],
			)
			array('TITLE', 'NAME', 'UF_CRM_1448279892')
		);
		//\cnLog::add('getList info24 lead res: '.print_r($res, true));
		if (!is_array($res) || empty($res['result'])) {
			$res = \CnInfo24Lead::add($arLead);
			cnLog::add('add info24 lead res: '.print_r($res, true));
		}
		*/

	}
}

saveSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth'], $botSettings['SETTINGS']);

// register new bot
$result = restCommand('imbot.register', Array(
	'CODE' => 'itrwaterbot',
	'TYPE' => 'B',
	'EVENT_MESSAGE_ADD' => $handlerBackUrl,
	'EVENT_WELCOME_MESSAGE' => $handlerBackUrl,
	'EVENT_BOT_DELETE' => $handlerBackUrl,
	'OPENLINE' => 'Y',
	'PROPERTIES' => Array(
		'NAME' => 'Доставка воды',
		'COLOR' => 'BLUE',
		'EMAIL' => 'info@info-expert.ru',
		'PERSONAL_BIRTHDAY' => '2016-03-11',
		'WORK_POSITION' => 'Помощник в заказе доставки воды',
		'PERSONAL_WWW' => 'http://bitrix24.com',
		'PERSONAL_GENDER' => 'M',
		'PERSONAL_PHOTO' => base64_encode(file_get_contents(__DIR__.'/avatar.png')),
	)
), $_REQUEST['auth']);

$botId = $result['result'];

// save params
/*
$appsConfig[$_REQUEST['auth']['application_token']] = Array(
	'BOT_ID' => $botId,
	'LANGUAGE_ID' => $_REQUEST['data']['LANGUAGE_ID'],
	'AUTH' => $_REQUEST['auth'],
);
saveParams($appsConfig);
*/

// $botSettings['SETTINGS']['FIRST'] = 0;
// saveSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth'], $botSettings['SETTINGS']);

foreach ($commands as $command) {
	$command['BOT_ID'] = $botId;
	$command['COMMON'] = 'N';
	$command['EVENT_COMMAND_ADD'] = $handlerBackUrl;
	$result = restCommand('imbot.command.register', $command, $_REQUEST['auth']);
	//$commandEcho = $result['result'];
}

if (!$result['error']) {
	$result = restCommand('imbot.message.add', Array(
	    'BOT_ID' => $botId,
	    'DIALOG_ID' => $_REQUEST['auth']['user_id'],
	    'MESSAGE' => 'Чат-бот "Доставка воды" установлен',
	    'SYSTEM' => 'Y'
	), $_REQUEST["auth"]);	
} else {
	$result = restCommand('im.notify', Array(
	    'TO' => $_REQUEST['auth']['user_id'],
	    'MESSAGE' => $result['error_description'],

	), $_REQUEST["auth"]);	
}