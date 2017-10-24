<?php

$chatType = explode('|', $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID']);
if ($chatType['0'] != 'livechat' && $chatType['0'] != 'network') {

    $resBasket = restCommand('entity.item.get', Array(
        'ENTITY' => BASKET_CODE,
        'FILTER' => array(
            'PROPERTY_BASKET_USER_ID' => $_REQUEST['data']['PARAMS']['CHAT_ENTITY_ID'],
        ),
        'SORT' => array(
            'DATE_ACTIVE_FROM' => 'ASC',
            'ID' => 'ASC',
        ),
    ), $_REQUEST['auth']);

    foreach ($resBasket['result'] as $basketKey => $basketValue) {
        $resBasketDelete = restCommand('entity.item.delete', Array(
            'ENTITY' => BASKET_CODE,
            'ID' => $basketValue['ID']
        ), $_REQUEST['auth']);
    }

    $mess = 'Ваша корзина пуста';
    $messCommand = '1: Оформить заказ[br]';
    $messCommand .= '0: Связаться с оператором';

    $result = restCommand('imbot.message.add', Array(
        "DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
        "MESSAGE" => $mess.'[br][br]'.$messCommand,
    ), $_REQUEST["auth"]);

}
