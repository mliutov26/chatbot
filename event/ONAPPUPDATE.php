<?php

// check the event - authorize this event or not
/*if (!isset($appsConfig[$_REQUEST['auth']['application_token']]))
	return false;*/

if ($_REQUEST['data']['VERSION'] == 2) {

	// Some logic in update event for VERSION 2
	// You can execute any method RestAPI, BotAPI or ChatAPI, for example delete or add a new command to the bot
	/*
	$result = restCommand('...', Array(
		'...' => '...',
	), $_REQUEST['auth']);
	*/

	/*
	For example delete 'Echo' command:

	$result = restCommand('imbot.command.unregister', Array(
		'COMMAND_ID' => $appsConfig[$_REQUEST['auth']['application_token']]['COMMAND_ECHO'],
	), $_REQUEST['auth']);
	*/

} else {

	// send answer message
	$result = restCommand('app.info', array(), $_REQUEST['auth']);

}

// write debug log
//writeToLog($result, 'ImBot update event');
