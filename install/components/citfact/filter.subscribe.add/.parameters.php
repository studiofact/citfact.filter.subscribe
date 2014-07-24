<?php

/*
 * This file is part of the Studio Fact package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=2132
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentParameters = array(
    'PARAMETERS' => array(
        'IBLOCK_ID' => array(
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('IBLOCK_ID'),
            'TYPE' => 'STRING',
            'VALUES' => '',
        ),
        'SECTION_ID' => array(
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('SECTION_ID'),
            'TYPE' => 'STRING',
            'VALUES' => '',
        ),
        'FILTER_NAME' => array(
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('FILTER_NAME'),
            'TYPE' => 'STRING',
            'VALUES' => '',
        ),
    )
);
?>