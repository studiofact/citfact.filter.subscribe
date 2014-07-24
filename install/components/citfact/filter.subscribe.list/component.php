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
use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\IBlock;
use Citfact\FilterSubscribe\Model\SubscribeUserTable;

Loc::loadMessages(__FILE__);
Loader::includeModule('citfact.filter.subscribe');

$app = Application::getInstance();
$request = $app->getContext()->getRequest();

$userId = $GLOBALS['USER']->GetId();
$queryBuilder = new Entity\Query(SubscribeUserTable::getEntity());
$queryBuilder->registerRuntimeField('filter', array(
    'data_type' => 'Citfact\FilterSubscribe\Model\SubscribeTable',
    'reference' => array('=this.FILTER_ID' => 'ref.ID'),
))
->setSelect(array('*', 'filter'))
->setFilter(array('USER_ID' => $userId));

$filterResult = $queryBuilder->exec();
while ($filter = $filterResult->fetch()) {
    $filter['DELETE_LINK'] = sprintf('%s?ACTION=DELETE&amp;ID=%d', getenv('SCRIPT_NAME'), $filter['ID']);
    $arResult['ITEMS'][] = $filter;
    if ($filter['SECTION_ID'] > 0) {
        $arResult['SECTION_ID'][] = $filter['SECTION_ID'];
    }
}

if (array_key_exists('ITEMS', $arResult)) {
    $filterLexer = new \Citfact\FilterSubscribe\FilterLexer();
    foreach ($arResult['ITEMS'] as $key => $filter) {
        $arResult['ITEMS'][$key]['FILTER_LINK'] = $filterLexer->getFilterUniqId(unserialize($filter['FILTER']));
        $filterLexer->addFilter($filter['FILTER'], true);
    }

    $filterLexer->parse();
    $arResult['FILTER'] = $filterLexer->getFilter();
    $arResult['FILTER_PROPERTY'] = $filterLexer->getProperty();
    $arResult['FILTER_PRICE_TYPE'] = $filterLexer->getPriceType();
    $arResult['FILTER_VALUE'] = $filterLexer->getValue();
}

if (array_key_exists('SECTION_ID', $arResult)) {
    $queryBuilder = new Entity\Query(IBlock\SectionTable::getEntity());
    $queryBuilder->setSelect(array('*'))->setFilter(array('ID' => $arResult['SECTION_ID']));
    $sectionResult = $queryBuilder->exec();
    while ($section = $sectionResult->fetch()) {
        $arResult['SECTIONS'][$section['ID']] = $section;
    }
}

$this->IncludeComponentTemplate();