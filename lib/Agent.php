<?php

/*
 * This file is part of the Studio Fact package.
 *
 * (c) Kulichkin Denis (onEXHovia) <onexhovia@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Citfact\FilterSubscribe;

use Bitrix\Main\Entity;
use Citfact\FilterSubscribe\Model;

class Agent
{
    /**
     * Method searches for a custom filter that, needs updating (notifications of new elements).
     * Custom filters are added to the stack of tasks.
     *
     * @param int $limitFilter
     * @return string
     */
    public static function findUserFilterNotify($limitFilter = 1, $limitFilterUser = 100)
    {
        $iblockElement = new \CIBlockElement();
        $connection = \Bitrix\Main\Application::getConnection();
        $sqlHelper = $connection->getSqlHelper();

        $limitFilter = (int)$limitFilter;
        $limitFilterUser = (int)$limitFilterUser;
        if ($limitFilterUser <= 0 || $limitFilterUser <= 0) {
            return;
        }

        $queryBuilder = new Entity\Query(Model\SubscribeTable::getEntity());
        $filterResult = $queryBuilder->setSelect(array('*'))
            ->setFilter(array('ACTIVE' => 'Y'))
            ->setLimit($limitFilter)
            ->exec();

        while ($filterRow = $filterResult->fetch()) {
            $filter['IBLOCK_ID'] = $filterRow['IBLOCK_ID'];
            if ($filterRow['SECTION_ID'] > 0) {
                $filter['SECTION_ID'] = $filterRow['SECTION_ID'];
            }

            $filter = array_merge($filter, unserialize($filterRow['FILTER']));
            $elementResult = $iblockElement->GetList(false, $filter, array('IBLOCK_ID'))->fetch();
            if ($elementResult['CNT'] <= 0) {
                continue;
            }

            // Choose filters which notification less,
            // than the number of elements with filter
            $sql = "
                SELECT DISTINCT t1.ID
                FROM b_citfact_filter_subscribe_user as t1
                LEFT OUTER JOIN b_citfact_filter_subscribe_notify as t2 ON (
                    t1.ID = t2.FILTER_USER_ID AND
                    t1.FILTER_ID = '" . $sqlHelper->forSql($filterRow['ID']) . "'
                )
                WHERE (SELECT count(ID) FROM b_citfact_filter_subscribe_notify as t3 WHERE t3.FILTER_USER_ID = t1.ID) < '" . $sqlHelper->forSql($elementResult['CNT']) . "'
                GROUP BY t1.ID
                LIMIT 0,".$limitFilterUser."
            ";

            // Adding custom filters to stack table
            $notifyList = (array)$connection->query($sql)->fetchAll();
            foreach ($notifyList as $key => $notify) {
                Model\SubscribeStackTable::add(array(
                    'FILTER_USER_ID' => $notify['ID'],
                    'ACTION' => 'UPDATE',
                ));
            }

            // If list for stack table is not empty, then deactivate filter
            if (sizeof($notifyList) > 0) {
                Model\SubscribeTable::update($filterRow['ID'], array(
                    'ACTIVE' => 'N'
                ));
            }
        }

        return "Citfact\\FilterSubscribe\\Agent::findUserFilterNotify($limitFilter)";
    }

    /**
     * TASK performs with ACTION = INSERT
     *
     * @param int $limit
     * @return string
     */
    public static function taskInsertElement($limit = 500)
    {
        if (($limit = (int)$limit) < 0) {
            return;
        }

        $iblockElement = new \CIBlockElement();
        $connection = \Bitrix\Main\Application::getConnection();
        $sqlHelper = $connection->getSqlHelper();

        // We get a task with the `ACTION` of `INSERT`
        $sql = "
            SELECT t1.ID as TASK_ID, t1.PARAMS, t1.FILTER_USER_ID, t3.*
            FROM b_citfact_filter_subscribe_stack as t1
            LEFT JOIN b_citfact_filter_subscribe_user as t2 ON t2.ID = t1.FILTER_USER_ID
            LEFT JOIN b_citfact_filter_subscribe as t3 ON t3.ID = t2.FILTER_USER
            WHERE t1.ACTION = 'INSERT' AND t3.ACTIVE = 'N'
            LIMIT 1
        ";

        $task = (array)$connection->query($sql)->fetch();
        if (!empty($task)) {
            $filter['IBLOCK_ID'] = $task['IBLOCK_ID'];
            if ($task['SECTION_ID'] > 0) {
                $filter['SECTION_ID'] = $task['SECTION_ID'];
            }

            $filter = array_merge($filter, unserialize($task['FILTER']));
            $elementResult = $iblockElement->GetList(array('ID' => 'ASC'), $filter, false, array('nTopCount' => $limit), array('ID'));
            while ($element = $elementResult->fetch()) {
                Model\SubscribeNotifyTable::add(array(
                    'FILTER_USER_ID' => $task['FILTER_USER_ID'],
                    'ELEMENT_ID' => $element['ID'],
                ));
            }

            Model\SubscribeStackTable::delete(array('ID' => $task['TASK_ID']));
        }

        return "Citfact\\FilterSubscribe\\Agent::taskInsertElement($limit)";
    }

    /**
     * TASK performs with ACTION = UPDATE
     *
     * @param int $limit
     * @return string
     */
    public static function taskUpdateElement($limit = 30)
    {
        return "Citfact\\FilterSubscribe\\Agent::taskUpdateElement()";
    }

    /**
     * Updates field `ACTIVE` on the filters
     *
     * @param int $limit
     * @return string
     */
    public static function updateFilterStatus($limit = 50)
    {
        if (($limit = (int)$limit) < 0) {
            return;
        }

        $connection = \Bitrix\Main\Application::getConnection();
        $sqlHelper = $connection->getSqlHelper();

        $sql = "
            SELECT t1.ID
            FROM b_citfact_filter_subscribe as t1
            LEFT JOIN b_citfact_filter_subscribe_user as t2 ON t1.ID = t2.FILTER_ID
            WHERE t1.ACTIVE = 'N' AND NOT EXISTS (
                SELECT t3.ID
                FROM b_citfact_filter_subscribe_stack as t3
                WHERE t3.FILTER_USER_ID = t2.ID
            )
            LIMIT 0,".$limit."
        ";

        $fitlerResult = $connection->query($sql);
        while ($filter = $fitlerResult->fetch()) {
            Model\SubscribeTable::update($filter['ID'], array('ACTIVE' => 'Y'));
        }

        return "Citfact\\FilterSubscribe\\Agent::updateFilterStatus($limit)";
    }

    /**
     * Remove filter, if him not used
     *
     * @param int $limit
     * @return string
     */
    public static function removeUnusedFilter($limit = 50)
    {
        if (($limit = (int)$limit) < 0) {
            return;
        }

        $connection = \Bitrix\Main\Application::getConnection();
        $sqlHelper = $connection->getSqlHelper();

        $sql = "
            SELECT t1.ID
            FROM b_citfact_filter_subscribe as t1
            WHERE NOT EXISTS (
                SELECT t2.ID
                FROM b_citfact_filter_subscribe_user as t2
                WHERE t2.FILTER_ID = t1.ID
            )
            LIMIT 0,".$limit."
        ";

        $fitlerResult = $connection->query($sql);
        while ($filter = $fitlerResult->fetch()) {
            Model\SubscribeTable::delete(array('ID' => $filter['ID']));
        }

        return "Citfact\\FilterSubscribe\\Agent::removeUnusedFilter($limit)";
    }
}