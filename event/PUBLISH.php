<?php

// This event is a CUSTOM event and is not sent from platform Bitrix24

// check the event - authorize this event or not
/*if (!isset($appsConfig[$_REQUEST['application_token']]))
	return false;*/

// send answer message
$result = restCommand('imbot.message.add', $_REQUEST['PARAMS'], $appsConfig[$_REQUEST['application_token']]['AUTH']);
