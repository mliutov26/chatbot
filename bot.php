<?php
error_reporting(0);

ini_set('display_errors', true);
Error_Reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_STRICT);

define('BACK_URL', 'https://b24go.com/rest-bot-water-ml/bot.php');
define('LIB_PATH', 'rest-bot-water-ml');

define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'].'/'.LIB_PATH);
define('EVENT_PATH', ROOT_PATH.'/event');
define('GETDIALOG_PATH', ROOT_PATH.'/get_dialog');

require_once($_SERVER['DOCUMENT_ROOT'].'/../php/lib/base/cn_log.php');

//require_once(ROOT_PATH.'/config.php');
require_once(ROOT_PATH.'/config_app.php');
require_once(ROOT_PATH.'/photoGeneration.php');

require_once(ROOT_PATH.'/commands_app.php');

if ($_REQUEST['event']) {
	require_once(EVENT_PATH.'/'.$_REQUEST['event'].'.php');
}

cnLog::Add('REQUEST: '.print_r($_REQUEST, true));

/**
 * Save application configuration.
 * WARNING: this method is only created for demonstration, never store config like this
 *
 * @param $params
 * @return bool
 */
 
/*function saveParams($params)
{
	$config = "<?php\n";
	$config .= '$appsConfig = '.var_export($params, true).";\n";
	$config .= '?>';

	file_put_contents(__DIR__.'/config.php', $config);

	return true;
}*/

/**
 * Send rest query to Bitrix24.
 *
 * @param $method - Rest method, ex: methods
 * @param array $params - Method params, ex: Array()
 * @param array $auth - Authorize data, ex: Array('domain' => 'https://test.bitrix24.com', 'access_token' => '7inpwszbuu8vnwr5jmabqa467rqur7u6')
 * @param boolean $authRefresh - If authorize is expired, refresh token
 * @return mixed
 */
function restCommand($method, array $params = Array(), array $auth, $authRefresh = true) {	

	$queryUrl = 'https://'.$auth['domain'].'/rest/'.$method;
	$queryData = http_build_query(array_merge($params, array('auth' => $auth['access_token'])));

	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_POST => 1,
		CURLOPT_HEADER => 0,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_SSL_VERIFYPEER => 1,
		CURLOPT_URL => $queryUrl,
		CURLOPT_POSTFIELDS => $queryData,
	));

	$result = curl_exec($curl);
	//cnLog::Add('restCommand curl_error: '.curl_error($curl));

	curl_close($curl);

	$result = json_decode($result, 1);

	if ($authRefresh && isset($result['error']) && in_array($result['error'], array('expired_token', 'invalid_token')))
	{
		$auth = restAuth($auth);
		if ($auth)
		{
			//cnLog::Add('restCommand '.curl_error($curl));
			$result = restCommand($method, $params, $auth, false);
		}
	}

	return $result;
}

/**
 * Get new authorize data if you authorize is expire.
 *
 * @param array $auth - Authorize data, ex: Array('domain' => 'https://test.bitrix24.com', 'access_token' => '7inpwszbuu8vnwr5jmabqa467rqur7u6')
 * @return bool|mixed
 */
function restAuth($auth) {
	
	if (!CLIENT_ID || !CLIENT_SECRET)
		return false;

	if(!isset($auth['refresh_token']) || !isset($auth['scope']) || !isset($auth['domain']))
		return false;

	$queryUrl = 'https://'.$auth['domain'].'/oauth/token/';
	$queryData = http_build_query($queryParams = array(
		'grant_type' => 'refresh_token',
		'client_id' => CLIENT_ID,
		'client_secret' => CLIENT_SECRET,
		'refresh_token' => $auth['refresh_token'],
		'scope' => $auth['scope'],
	));
	
	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_HEADER => 0,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => $queryUrl.'?'.$queryData,
	));

	$result = curl_exec($curl);
	curl_close($curl);

	$result = json_decode($result, 1);
	
	if (!isset($result['error']))
	{
		/*$appsConfig = Array();
		if (file_exists(__DIR__.'/config.php'))
			include(__DIR__.'/config.php');*/

		$result['application_token'] = $auth['application_token'];
		/*$appsConfig[$auth['application_token']]['AUTH'] = $result;
		saveParams($appsConfig);*/
	}
	else
	{
		$result = false;
	}

	return $result;
}

/**
 * Create infoblock
 *
 * @param const $codeEntity
 * @param array $nameEntity
 * @param array $nameEntity
 * @return bool
 */
function createRemoteSettings($codeEntity, $nameEntity, $arPropsToAdd, $arAuth) {

	//cnLog::Add('Entity code: '.$codeEntity);

	$result = restCommand('entity.get',
		Array(
			'ENTITY' => $codeEntity,
		),
		$arAuth
	);

	//cnLog::Add('Entity.get. res: '.print_r($result, true));

	if (!array_key_exists('error', $result) || empty($result['error'])) {

		$result = restCommand('entity.delete',
			Array(
				'ENTITY' => $codeEntity,
			),
			$arAuth
		);
		if (array_key_exists('error', $result) && !empty($result['error'])) {
			//cnLog::Add('Entity.delete. res: '.print_r($result, true));
			return;
		}
	}


	$result = restCommand('entity.add',
		Array(
			'ENTITY' => $codeEntity,
			'NAME' => $nameEntity,
			'ACCESS' => array('U1'=>'X', 'AU'=>'W'),
		),
		$arAuth
	);

	//cnLog::Add('Entity.add. res: '.print_r($result, true));

	if (array_key_exists('result', $result) && $result['result']) {
		foreach($arPropsToAdd as $code => $arP) {
			$arP['ENTITY'] = $codeEntity;

			$resProp = restCommand('entity.item.property.add',
				$arP,
				$arAuth
			);

			//cnLog::Add('entity.item.property.add res: '.print_r($resProp, true).' Entity code: '.$codeEntity);
		}
	}
}


function getUsers($arAuth, $filter = array()) {	
	$arFilter = array('ACTIVE' => 'Y');
	$arFilter = array_merge($arFilter, $filter);

	$result = restCommand('user.get', Array(
		'SORT' => 'LAST_NAME',
		'ORDER' => 'ASC',
		'FILTER' => $arFilter,
	), $arAuth);

	$portalUsers = array();

	if (array_key_exists('result', $result) && $result['result']) {
		foreach($result['result'] as $key => $user) {
			$portalUsers[$user['ID']] = $user['LAST_NAME'].' '.$user['NAME'];
		}
	}

	//cnLog::Add('getUsers1: '.print_r($result, true));
	//cnLog::Add('getUsers2: '.print_r($portalUsers, true));
	return $portalUsers;
}

function loadSettings($entity, $code, $arAuth, $bRefresh = false) {
	static $arSettings;
	if($bRefresh){
		$arSettings = null;
	}
	if(is_array($arSettings) && array_key_exists($code, $arSettings)) {
		return $arSettings[$code];
	}
	if(!is_array($arSettings)){
		$arSettings = array();
	}

	$result = restCommand('entity.item.get', Array(
		'ENTITY' => $entity,
		'FILTER' => array('CODE' => $code)
	), $arAuth);

	if(empty($result['result'][0])) { 
		return array();
	} else {
		$arSettings[$code] = array(
			'ID' => $result['result'][0]['ID'],
			'SETTINGS' => (strlen($result['result'][0]['DETAIL_TEXT'])) ? unserialize($result['result'][0]['DETAIL_TEXT']) : array(),
		);
		return $arSettings[$code];
	}
}

function saveSettings($entity, $code, $arAuth, $arSettings) {	

	$curSettings = loadSettings($entity, $code, $arAuth);

	//cnLog::Add('$curSettings: '.print_r($curSettings, true));

	if(empty($curSettings)) {
		$resSave = restCommand('entity.item.add', Array(
			'ENTITY' => $entity,
			'NAME' => 'Настройки приложения',
			'CODE' => $code,
			'DETAIL_TEXT' => serialize($arSettings),
		), $arAuth);
	} else {
		$newParams = array_merge($curSettings['SETTINGS'], $arSettings);
		$resSave = restCommand('entity.item.update', Array(
			'ENTITY' => $entity,
			'ID' => $curSettings['ID'],
			'DETAIL_TEXT' => serialize($newParams),
			//'DETAIL_TEXT' => serialize(array()),
		), $arAuth);
	}

	return !empty($resSave['result']);
}

function resetSettings($entity, $code, $arAuth) {	

	$curSettings = loadSettings($entity, $code, $arAuth);

	//cnLog::Add('$curSettings: '.print_r($curSettings, true));
	$resSave = restCommand('entity.item.update', Array(
		'ENTITY' => $entity,
		'ID' => $curSettings['ID'],
		'DETAIL_TEXT' => serialize(array()),
	), $arAuth);

	loadSettings($entity, $code, $arAuth, true);

	return !empty($resSave['result']);
}

function getCommandBtn($arrCommandsName, $commands) {
	$strButtons = '';
	foreach ($arrCommandsName as $key => $commandName) {
		if(array_key_exists($commandName, $commands)) {
			$strButtons .= '[br][send=/'.$commands[$commandName]['COMMAND'].']'.$commands[$commandName]['LANG'][1]['TITLE'].'[/send]'; 
		}
	}
	return $strButtons;
}

function getDialog($dialogId, $message, $event, $messageId, $arAuth) {
	
	$dialogSettings = loadSettings(WBOT_CODE, $dialogId, $arAuth);
	$arrMessage = explode(" ", $message);

	if($event == 'ONIMBOTMESSAGEADD' && empty($dialogSettings['SETTINGS'])) {
		$notFoundRequest = restCommand('imbot.message.add', Array(
			'DIALOG_ID' => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
			'MESSAGE' => 'Ошибка запроса. Проверьте корректность ввода команды',
		), $_REQUEST['auth']);
		return;
	}
	
	if(empty($dialogSettings['SETTINGS'])){
		return;
	}

	$curState = $dialogSettings['SETTINGS']['STATE'];
	$expectedOption = $dialogSettings['SETTINGS']['PARAM'];
	$productId = $dialogSettings['SETTINGS']['ID'];
	
	if($event == 'ONIMCOMMANDADD') {
		$command = current($_REQUEST['data']['COMMAND']);
	}

	if (!file_exists(EVENT_PATH.'/onImCommandAdd/'.current($arrMessage).'.php') && empty($dialogSettings['SETTINGS']['STATE'])) {
		$notFoundRequest = restCommand('imbot.message.add', Array(
			'DIALOG_ID' => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
			'MESSAGE' => 'Ошибка запроса. Проверьте корректность ввода команды',
		), $_REQUEST['auth']);
		exit;
	}

	if ($curState) {
		require_once(GETDIALOG_PATH.'/'.$curState.'.php');
	}
    
}

function convertFromUtf($str) {
	$str = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
			return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
		},
		$str
	);
	return $str;
}

function saveProductImage($productId, $attachFile, $message, $dialogId, $entity, $arAuth) {
	//cnLog::Add('saveProductImage $attachFile: '.print_r($attachFile, true));
	//cnLog::Add('saveProductImage request data: '.print_r($_REQUEST['data']['PARAMS'], true));

	$resProduct = restCommand('entity.item.get', Array(
		'ENTITY' => $entity,
		'FILTER' => array(
			'ID' => $productId,
		),
	), $arAuth);

	//cnLog::Add('saveProductImage resProduct: '.print_r($resProduct['result'], true));

	$itemData = current($resProduct['result']);

	if($attachFile['id']) {

		if($attachFile['type'] !== 'image') {
			$result = restCommand('imbot.message.add', Array(
				'DIALOG_ID' => $dialogId,
				'MESSAGE' => 'Неверный тип файла. Ожидается картинка. Попробуйте еще раз или [send=/exit_state]выйдите из режима[/send]',
			), $arAuth);
			exit;
		}

		$resFile = restCommand('disk.file.get', Array('id' => $attachFile['id']), $arAuth);

		//cnLog::Add('saveProductImage $resFile: '.print_r($resFile, true));

		if(array_key_exists('error', $result) && !empty($result['error'])) {
			$result = restCommand('imbot.message.add', Array(
				'DIALOG_ID' => $dialogId,
				'MESSAGE' => 'Не удалось получить изображение. Попробуйте еще раз или [send=/exit_state]выйдите из режима[/send]',
			), $arAuth);
			exit;
		} 

		$urlImg = base64_encode(file_get_contents($resFile['result']['DOWNLOAD_URL']));
		$nameImg = $resFile['result']['NAME'];

		//cnLog::Add('saveProductImage $urlImg: '.strlen($urlImg));

		$resUpdateImg = restCommand('entity.item.update', Array(
			'ENTITY' => $entity,
			'ID' => $productId,
			'PREVIEW_PICTURE' => array($nameImg, $urlImg),
			'DETAIL_PICTURE' => array($nameImg, $itemImgUrl),
		), $arAuth);

		//cnLog::Add('saveProductImage $resUpdateImg: '.print_r($resUpdateImg, true));

		$resGetImg = restCommand('entity.item.get', Array(
			'ENTITY' => $entity,
			'ID' => $productId,
		), $arAuth);

		//cnLog::Add('saveProductImage $resGetImg: '.print_r($resGetImg, true));

		if(array_key_exists('error', $result) && !empty($result['error'])) {
			$result = restCommand('imbot.message.add', Array(
				'DIALOG_ID' => $dialogId,
				'MESSAGE' => 'Не удалось сохранить изображение. Попробуйте еще раз или [send=/exit_state]выйдите из режима[/send]',
			), $arAuth);
			exit;
		} 

	} else {
		//cnLog::Add('getDialog image URL: '.print_r($message, true));
		preg_match('/\[URL=([^\]]+)\]/', $message, $matches);

		if (preg_match('/\[URL=([^\]]+)\]/', $message, $matches)) {
			validateLink($matches[1], $dialogId, $arAuth);

			$itemImgUrl = $matches[1];

			$itemPrice = $itemData['PROPERTY_VALUES']['PRODUCT_PRICE'];
			$itemVolume = (array_key_exists(PRODUCT_VOLUME, $itemData['PROPERTY_VALUES'])) ? $itemData['PROPERTY_VALUES']['PRODUCT_VOLUME'] : "";
			$itemName = $itemData['NAME'];

			$image = imageGeneration(
				$itemImgUrl, // input photo file
				ROOT_PATH.'/outputPhoto.jpg', // output photo file
				ROOT_PATH.'/font/DroidSansMono.ttf', // ttf font file 1
				ROOT_PATH.'/font/PTSans.ttf', // ttf font file 2
				$itemName, // item name
				$itemPrice, // item price
				$itemVolume, // item V
				'#000' // font color
			);
			
			if (!$image) {
				
				$resultEditWater = restCommand('imbot.message.add', Array(
					'DIALOG_ID' => $dialogId,
					'MESSAGE' => 'Ошибка загрузки изображения. Попробуйте еще раз или [send=/exit_state]выйдите из режима[/send]',
				), $arAuth);
				
			} else {
			
				$urlImg = base64_encode(file_get_contents(__DIR__.'/outputPhoto.jpg'));
				$urlDetailImg = base64_encode(file_get_contents($itemImgUrl));
				$nameImg = urldecode(basename($matches[1]));
				
				$resUpdateImg = restCommand('entity.item.update', Array(
					'ENTITY' => $entity,
					'ID' => $productId,
					'PREVIEW_PICTURE' => array(
						$nameImg, $urlImg
					),
					'DETAIL_PICTURE' => array(
						$nameImg, $urlDetailImg
					),
				), $arAuth);
	
				if(array_key_exists('error', $resUpdateImg) && !empty($resUpdateImg['error'])) {
					$resultEditWater = restCommand('imbot.message.add', Array(
						'DIALOG_ID' => $dialogId,
						'MESSAGE' => 'Ошибка загрузки изображения. Попробуйте еще раз или [send=/exit_state]выйдите из режима[/send]',
					), $arAuth);
					cnLog::Add('$resultEditWater:', $resUpdateImg['error']);
					exit;
				}
				
			}
		}
		else {
			$resultEditWater = restCommand('imbot.message.add', Array(
				'DIALOG_ID' => $dialogId,
				'MESSAGE' => 'Нет ссылки на изображение. Попробуйте еще раз или [send=/exit_state]выйдите из режима[/send]',
			), $arAuth);
			exit;
		}
	}
}

/**
 * Проверка на число
 *
 * @param string $message
 * @param number $dialogId
 * @param array $arAuth
 */
function validateNumber($message, $dialogId, $arAuth) {
	if(!is_numeric($message)) {
		$resultEditWater = restCommand('imbot.message.add', Array(
			'DIALOG_ID' => $dialogId,
			'MESSAGE' => 'Неверный формат данных. Введите целое число',
		), $arAuth);
		exit;
	}
}


/**
 * Проверка ссылки на картинку
 *
 * @param string $link
 * @param number $dialogId
 * @param array $arAuth
 */
function validateLink($link, $dialogId, $arAuth) {
	$extension = end(explode('.', $link));
	$arrExtensions = array('jpg', 'jpeg', 'png', 'gif', 'bmp');
	if(!in_array(strtolower($extension), $arrExtensions)) {
		echo 'неверный тип файла';
		$resultEditWater = restCommand('imbot.message.add', Array(
			'DIALOG_ID' => $dialogId,
			'MESSAGE' => 'Неверный тип файла![br]Введите ссылку на изображение (окончание ссылки должно быть .png, .jpg, .gif) или [send=/exit_state]выйдите из режима[/send]',
		), $arAuth);
		exit;
	}
}
