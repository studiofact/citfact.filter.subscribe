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
use Bitrix\Main\Entity;
use Citfact\FilterSubscribe\Model\SubscribeUserTable;

Loc::loadMessages(__FILE__);
Loader::includeModule('citfact.filter.subscribe');

$app = Application::getInstance();
$request = $app->getContext()->getRequest();

if (!$GLOBALS['USER']->IsAuthorized()) {
    return;
}

$isAjax = (getenv('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest');
$subscribeManager = new \Citfact\FilterSubscribe\SubscribeManager();

// Check whether there is a user filter
if ($request->getQuery('CHECK_FILTER') && $isAjax) {
    $filterData = $GLOBALS[$arParams['FILTER_NAME']];
    $filterData = $subscribeManager->normalizeFilter($filterData);
    $queryBuilder = new Entity\Query(SubscribeUserTable::getEntity());
    $filter = $queryBuilder
        ->registerRuntimeField('filter', array(
            'data_type' => 'Citfact\FilterSubscribe\Model\SubscribeTable',
            'reference' => array('=this.FILTER_ID' => 'ref.ID'),
        ))
        ->setSelect(array('*', 'filter'))
        ->setFilter(array('USER_ID' => $GLOBALS['USER']->GetId(), 'filter.FILTER' => $filterData))
        ->setLimit(1)
        ->exec()
        ->fetch();

    $GLOBALS['APPLICATION']->RestartBuffer();
    header('Content-Type: application/json');
    exit(json_encode(array('data' => $filter)));
}

// Saves the current filter,
// If such a filter not already exists
if ($request->getQuery('SAVE_FILTER') && $isAjax) {
    $filterData = $GLOBALS[$arParams['FILTER_NAME']];
    $filterResult = $subscribeManager->addFilter(array(
        'FILTER' => $filterData,
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
        'SECTION_ID' => $arParams['SECTION_ID'],
    ));

    $filterUserResult = $subscribeManager->addFilterUser(array(
        'USER_ID' => $GLOBALS['USER']->GetId(),
        'FILTER_ID' => $filterResult->getId(),
    ));

    $GLOBALS['APPLICATION']->RestartBuffer();
    header('Content-Type: application/json');
    exit(json_encode(array(
        'success' => ($filterResult->isSuccess() && $filterUserResult->isSuccess())
    )));
}

$this->IncludeComponentTemplate();