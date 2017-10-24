<?php

$resultRefund = restCommand('imbot.message.add', Array(
	'DIALOG_ID' => $dialogId,
	'MESSAGE' => 'Все будет хорошо :)',
), $arAuth);
