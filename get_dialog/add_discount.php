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
		'COMMAND_PARAMS' => 'add_water water accessories delivery discount refund fine admins hello settings',
	),
	Array(
		'TEXT' => 'Сбросить все настройки',
		'BG_COLOR' => '#aeb1b7',
		'TEXT_COLOR' => '#f7f6f6',
		'DISPLAY' => 'LINE',
		'COMMAND' => 'reset_settings',
	),*/
);

switch ($expectedOption) {
	case 'discount_summ':
		if($event == 'ONIMCOMMANDADD') {
			if($command['COMMAND'] !== 'exit_state' && $command['COMMAND'] !== 'reset_settings') {
				$result = restCommand('imbot.command.answer', Array(
					'COMMAND_ID' => $command['COMMAND_ID'],
					'MESSAGE_ID' => $messageId,
					'MESSAGE' => 'Бот находится в режиме ввода пороговой суммы для скидки. Введите сумму (только число) или [send=/exit_state]выйдите из режима[/send]',
				), $arAuth);
				exit;
			}
		} else {
			validateNumber($message, $dialogId, $arAuth);

			$dialogSettings['SETTINGS']['DISCOUNT_SUMM'] = $message;
			$dialogSettings['SETTINGS']['PARAM'] = 'discount_sale';
			saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);
			$resultRefund = restCommand('imbot.message.add', Array(
				'DIALOG_ID' => $dialogId,
				'MESSAGE' => 'Введите размер скидки в % (только число)',
			), $arAuth);
			exit;
		}
		break;
	case 'discount_sale':
		if($event == 'ONIMCOMMANDADD') {
			if($command['COMMAND'] !== 'exit_state' || $command['COMMAND'] !== 'reset_settings') {
				$result = restCommand('imbot.command.answer', Array(
					'COMMAND_ID' => $command['COMMAND_ID'],
					'MESSAGE_ID' => $messageId,
					'MESSAGE' => 'Бот находится в режиме ввода размера скидки. Введите размер скидки (только число) или [send=/exit_state]выйдите из режима[/send]',
				), $arAuth);
				exit;
			}
		} else {
			validateNumber($message, $dialogId, $arAuth);

			$discountSumm = $dialogSettings['SETTINGS']['DISCOUNT_SUMM'];
			$discountSale = $message;
			$botSettings['SETTINGS']['DISCOUNT'] = array($discountSumm => $discountSale);

			saveSettings(ADMIN_SETTINGS, ADMIN_SETTINGS_EL, $_REQUEST['auth'], $botSettings['SETTINGS']);

			unset($dialogSettings['SETTINGS']['DISCOUNT_SUMM']);
			$dialogSettings['SETTINGS'] = array(
				'STATE' => '',
				'PARAM' => '',
				'ID' => ''
			);
			saveSettings(WBOT_CODE, $dialogId, $_REQUEST['auth'], $dialogSettings['SETTINGS']);

			$resultRefund = restCommand('imbot.message.add', Array(
				'DIALOG_ID' => $dialogId,
				'MESSAGE' => 'Скидка добавлена. [send=/discount]Посмотреть[/send]',
			), $arAuth);
			exit;
		}
		break;
}
