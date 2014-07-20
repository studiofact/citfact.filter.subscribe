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

Loader::registerAutoLoadClasses('citfact.filter.subscribe', array(
    'Citfact\FilterSubscribe\Model\FilterSubscribe' => 'lib/Model/FilterSubscribe.php',
    'Citfact\FilterSubscribe\Model\FilterSubscribeNotify' => 'lib/Model/FilterSubscribeNotify.php',
    'Citfact\FilterSubscribe\Model\FilterSubscribeStack' => 'lib/Model/FilterSubscribeStack.php',
    'Citfact\FilterSubscribe\Model\FilterSubscribeUser' => 'lib/Model/FilterSubscribeUser.php',
    'Citfact\FilterSubscribe\Agent' => 'lib/Agent.php',
));
