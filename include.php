<?php

/*
 * This file is part of the Studio Fact package.
 *
 * (c) Kulichkin Denis (onEXHovia) <onexhovia@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Bitrix\Main\Loader;

Loader::includeModule('catalog');
Loader::registerAutoLoadClasses('citfact.filter.subscribe', array(
    'Citfact\FilterSubscribe\Model\SubscribeTable' => 'lib/Model/SubscribeTable.php',
    'Citfact\FilterSubscribe\Model\SubscribeNotifyTable' => 'lib/Model/SubscribeNotifyTable.php',
    'Citfact\FilterSubscribe\Model\SubscribeStackTable' => 'lib/Model/SubscribeStackTable.php',
    'Citfact\FilterSubscribe\Model\SubscribeUserTable' => 'lib/Model/SubscribeUserTable.php',
    'Citfact\FilterSubscribe\Agent' => 'lib/Agent.php',
    'Citfact\FilterSubscribe\FilterLexer' => 'lib/FilterLexer.php',
    'Citfact\FilterSubscribe\SubscribeManager' => 'lib/SubscribeManager.php',
));
