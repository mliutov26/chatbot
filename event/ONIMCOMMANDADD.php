<?php

define('COMADDEVENT_PATH', ROOT_PATH.'/event/onImCommandAdd');

//** UPDATE ADMIN TOKEN
if ($botSettings['SETTINGS']['ADMINS'][$_REQUEST['auth']['user_id']]) {
	$mysqli = mysqli_connect(SQL_SERVER, SQL_LOGIN, SQL_PASSWORD, SQL_TABLE);
	if ($mysqli) {
		$updateQuery = mysqli_query($mysqli, 'UPDATE cn_mega_portal_reg SET ACCESS_TOKEN = "'.$_REQUEST['auth']['access_token'].'", EXPIRES_IN = "'.$_REQUEST['auth']['expires_in'].'", REFRESH_TOKEN = "'.$_REQUEST['auth']['refresh_token'].'", MEMBER_ID = "'.$_REQUEST['auth']['member_id'].'", DATE_UPDATE = "'.date("Y-m-d H:i:s").'" WHERE APP_CODE = "waterbot" AND URL = "'.$_REQUEST['auth']['domain'].'"');
		mysqli_close($updateQuery);	
	}
}

// check the event - authorize this event or not
/*if (!isset($appsConfig[$_REQUEST['auth']['application_token']]))
	return false;*/

if ($botSettings['SETTINGS']['ADMIN_CRM']) {

	$result = false;
	
	$chatUserId = $_REQUEST['data']['USER']['ID'];
	$arrAdmins = $botSettings['SETTINGS']['ADMINS'];
	$curPortalAdminId = $botSettings['SETTINGS']['CURR_ADMIN_ID'];
	
	if(array_key_exists($chatUserId, $arrAdmins) || $chatUserId == $curPortalAdminId) {
	
		$dialogId = $_REQUEST['data']['PARAMS']['DIALOG_ID'];
		$message = $_REQUEST['data']['PARAMS']['MESSAGE'];
		$messageId = $_REQUEST['data']['PARAMS']['MESSAGE_ID'];
		getDialog($dialogId, $message, $_REQUEST['event'], $messageId, $_REQUEST['auth']);
	
		foreach ($_REQUEST['data']['COMMAND'] as $command) {
			if (file_exists(COMADDEVENT_PATH.'/'.$command['COMMAND'].'.php')) {
				require_once(COMADDEVENT_PATH.'/'.$command['COMMAND'].'.php');
			} else {
				$notFoundRequest = restCommand('imbot.message.add', Array(
					'DIALOG_ID' => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
					'MESSAGE' => 'Ошибка запроса. Проверьте корректность ввода команды',
				), $_REQUEST['auth']);
			}
		}
	
	} else {
		
		$result = restCommand('imbot.message.add', Array(
			'DIALOG_ID' => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
			'MESSAGE' => 'Команды доступны только администраторам приложения',
		), $_REQUEST['auth']);
		
	}
	
} else {
	
	$result = restCommand('imbot.message.add', Array(
		'DIALOG_ID' => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
		'MESSAGE' => 'Переустановите бота под пользователем с доступом в CRM',
	), $_REQUEST['auth']);
		
}
