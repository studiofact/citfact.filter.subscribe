<?php

/*
 * This file is part of the Studio Fact package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);
Loader::includeModule('citfact.filter.subscribe');

$app = Application::getInstance();
$request = $app->getContext()->getRequest();

if ($request->getQuery('SAVE_FILTER') && getenv('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest') {
    $filterData = $GLOBALS[$arParams['FILTER_NAME']];
    foreach ($filterData as $key => $value) {
        if (!preg_match('#PROPERTY|CATALOG_PRICE|OFFERS#', $key)) {
            $filterData[$key] = $value;
        }
    }

    $subscribeManager = new \Citfact\FilterSubscribe\SubscribeManager();
    $filterId = $subscribeManager->addFilter(array(
        'FILTER' => $filterData,
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
        'SECTION_ID' => $arParams['SECTION_ID'],
    ));

    $filterUserId = $subscribeManager->addFilterUser(array(
        'USER_ID' => $GLOBALS['USER']->GetId(),
        'FILTER_ID' => $filterId,
    ));

    $GLOBALS['APPLICATION']->RestartBuffer();
    header('Content-Type: application/json');
    exit(json_encode(array(
        'success' => ($filterId && $filterUserId)
    )));
}

$this->IncludeComponentTemplate();