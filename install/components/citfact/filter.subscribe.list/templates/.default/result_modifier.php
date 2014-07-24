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

$arResult['ITEMS_CLEAR'] = array();
foreach ($arResult['ITEMS'] as $key => $item) {
    $filterParams = $arResult['FILTER'][$item['FILTER_LINK']];
    foreach ($filterParams as $param => $valueList) {
        $property = null;

        if (preg_match('/PROPERTY/', $param)) {
            $property = $arResult['FILTER_PROPERTY'][$param];
            $tempory['LABEL'] = $property['NAME'];
        } elseif (preg_match('/CATALOG_PRICE/i', $param)) {
            $tempory['LABEL'] = GetMessage('PRICE');
        }

        if (is_array($property) && in_array($property['PROPERTY_TYPE'], array('E', 'G', 'L'))) {
            switch ($property['PROPERTY_TYPE']) {
                case 'E': $valueStorage = $arResult['FILTER_VALUE']['ELEMENT']; break;
                case 'G': $valueStorage = $arResult['FILTER_VALUE']['SECTION']; break;
                case 'L': $valueStorage = $arResult['FILTER_VALUE']['LIST']; break;
            }

            foreach ($valueList as $index => $value) {
                $valueList[$index] = ($property['PROPERTY_TYPE'] == 'G')
                    ? $valueStorage[$value]['VALUE']
                    : $valueStorage[$value]['NAME'];
            }
        }

        if (is_null($property)) {
            $tempory['VALUE'] = implode(' - ', $valueList).' '.GetMessage('RUB');
        } else {
            $tempory['VALUE'] = implode(', ', $valueList);
        }

        $tempory['DELETE_LINK'] = $item['DELETE_LINK'];
        $arResult['ITEMS_CLEAR'][$key][] = $tempory;
    }
}
