<?php

/*
 * This file is part of the Studio Fact package.
 *
 * (c) Kulichkin Denis (onEXHovia) <onexhovia@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Citfact\FilterSubscribe\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SubscribeTable extends Entity\DataManager
{
    /**
     * {@inheritdoc}
     */
    public static function getFilePath()
    {
        return __FILE__;
    }

    /**
     * {@inheritdoc}
     */
    public static function getTableName()
    {
        return 'b_citfact_filter_subscribe';
    }

    /**
     * {@inheritdoc}
     */
    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
            ),
            'ACTIVE' => array(
                'data_type' => 'boolean',
                'values' => array('N','Y'),
            ),
            'FILTER' => array(
                'data_type' => 'string',
                'required' => true,
            ),
            'IBLOCK_ID' => array(
                'data_type' => 'integer',
                'required' => true,
            ),
            'SECTION_ID' => array(
                'data_type' => 'integer',
            ),
            'TIMESTAMP_X' => array(
                'data_type' => 'datetime',
            ),
        );
    }
}