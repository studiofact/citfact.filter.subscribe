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
use Bitrix\Iblock;
use Citfact\FilterSubscribe\Model;

class SubscribeManager
{
    /**
     * Save filter, requirement fields: IBLOCK_ID, FILTER
     *
     * @param array $fields
     * @return \Bitrix\Main\Entity\AddResult
     * @throws \InvalidArgumentException
     */
    public function addFilter(array $fields)
    {
        $filter = $fields['FILTER'];
        if (!is_array($filter) || sizeof($filter) <= 0) {
            throw new \InvalidArgumentException('Filter can not be empty');
        }

        $filter = $this->normalizeFilter($filter);
        $iblockId = (int)$fields['IBLOCK_ID'];
        $sectionId = (int)$fields['SECTION_ID'];
        $queryBuilder = new Entity\Query(Iblock\IblockTable::getEntity());
        $iblockDataResult = $queryBuilder->setSelect(array('ID'))
            ->setFilter(array('ID' => $iblockId))
            ->exec()
            ->fetch();

        if ($this->isEmptyResult($iblockDataResult)) {
            throw new \InvalidArgumentException('Invalid IBLOCK_ID');
        }

        if ($sectionId > 0) {
            $queryBuilder = new Entity\Query(Iblock\SectionTable::getEntity());
            $sectionDataResult = $queryBuilder->setSelect(array('ID'))
                ->setFilter(array('IBLOCK_ID' => $iblockDataResult['ID'], 'ID' => $sectionId))
                ->exec()
                ->fetch();

            if ($this->isEmptyResult($sectionDataResult)) {
                throw new \InvalidArgumentException('Invalid SECTION_ID');
            }
        }

        $queryBuilder = new Entity\Query(Model\SubscribeTable::getEntity());
        $subscribe = $queryBuilder->setSelect(array('ID'))
            ->setFilter(array(
                'FILTER' => $filter,
                'IBLOCK_ID' => $iblockDataResult['ID'],
                'SECTION_ID' => isset($sectionDataResult) ? $sectionDataResult['ID'] : '',
            ))
            ->exec()
            ->fetch();

        if (!empty($subscribe)) {
            $addResult = new \Bitrix\Main\Entity\AddResult();
            $addResult->setId($subscribe['ID']);

            return $addResult;
        }

        $subscribeResult = Model\SubscribeTable::add(array(
            'FILTER' => $filter,
            'IBLOCK_ID' => $iblockDataResult['ID'],
            'SECTION_ID' => isset($sectionDataResult) ? $sectionDataResult['ID'] : '',
        ));

        return $subscribeResult;
    }

    /**
     * Save link user filter with filter
     * Requirement fields: USER_ID, FILTER_ID
     *
     * @param array $fields
     * @return \Bitrix\Main\Entity\AddResult
     * @throws \InvalidArgumentException
     */
    public function addFilterUser(array $fields)
    {
        $userId = $fields['USER_ID'];
        $userData = \CUser::GetById($userId)->GetNext();
        if ($this->isEmptyResult($userData)) {
            throw new \InvalidArgumentException('Invalid USER_ID');
        }

        $queryBuilder = new Entity\Query(Model\SubscribeTable::getEntity());
        $subscribe = $queryBuilder->setSelect(array('ID'))
            ->setFilter(array('ID' => $fields['FILTER_ID']))
            ->exec()
            ->fetch();

        if ($this->isEmptyResult($subscribe)) {
            throw new \InvalidArgumentException('Invalid FILTER_ID');
        }

        $filterUserData = Model\SubscribeUserTable::getList(array(
            'select' => array('ID'),
            'filter' => array(
                'USER_ID' => $userId,
                'FILTER_ID' => $subscribe['ID']
            )
        ))->fetch();

        if (!$this->isEmptyResult($filterUserData)) {
            $addResult = new \Bitrix\Main\Entity\AddResult();
            $addResult->setId($filterUserData['ID']);

            return $addResult;
        }

        $filterUserResult = Model\SubscribeUserTable::add(array(
            'USER_ID' => $userData['ID'],
            'FILTER_ID' => $subscribe['ID']
        ));

        if ($filterUserResult->isSuccess()) {
            Model\SubscribeStackTable::add(array(
                'FILTER_USER_ID' => $filterUserResult->getId(),
                'ACTION' => 'INSERT'
            ));
        }

        return $filterUserResult;
    }

    /**
     * Remove filter snapshot
     *
     * @param int $id
     * @return \Bitrix\Main\Entity\DeleteResult
     * @throws \InvalidArgumentException
     */
    public function removeFilter($id)
    {
        $filter = Model\SubscribeTable::getById($id)->fetch();
        if ($this->isEmptyResult($filter)) {
            throw new \InvalidArgumentException('Invalid filter id');
        }

        $queryBuilder = new Entity\Query(Model\SubscribeUserTable::getEntity());
        $subscribeUser = $queryBuilder->setSelect(array('ID'))
            ->setFilter(array('FILTER_ID' => $filter['ID']))
            ->exec();

        $subscribeUserList = array();
        while ($row = $subscribeUser->fetch()) {
            $subscribeUserList[] = $row['ID'];
            Model\SubscribeUserTable::delete(array('ID' => $row['ID']));
        }

        $queryBuilder = new Entity\Query(Model\SubscribeStackTable::getEntity());
        $subscribeStack = $queryBuilder->setSelect(array('ID'))
            ->setFilter(array('FILTER_USER_ID' => $subscribeUserList))
            ->exec();

        while ($row = $subscribeStack->fetch()) {
            Model\SubscribeStackTable::delete(array('ID' => $row['ID']));
        }

        $result = Model\SubscribeTable::delete($id);

        return $result;
    }

    /**
     * Remove filter user and relationships
     *
     * @param int $id
     * @return \Bitrix\Main\Entity\DeleteResult
     * @throws \InvalidArgumentException
     */
    public function removeFilterUser($id)
    {
        $filterUser = Model\SubscribeUserTable::getById($id)->fetch();
        if ($this->isEmptyResult($filterUser)) {
            throw new \InvalidArgumentException('Invalid filter user id');
        }

        $queryBuilder = new Entity\Query(Model\SubscribeStackTable::getEntity());
        $subscribeStack = $queryBuilder->setSelect(array('ID'))
            ->setFilter(array('FILTER_USER_ID' => $filterUser['ID']))
            ->exec();

        while ($row = $subscribeStack->fetch()) {
            Model\SubscribeStackTable::delete(array('ID' => $row['ID']));
        }

        $result = Model\SubscribeUserTable::delete(array('ID' => $filterUser['ID']));
        $queryBuilder = new Entity\Query(Model\SubscribeUserTable::getEntity());
        $filterUserResult = $queryBuilder
            ->registerRuntimeField('cnt', array(
                'data_type' => 'integer',
                'expression' => array('count(%s)', 'ID')
            ))
            ->setSelect(array('ID', 'cnt'))
            ->setFilter(array('FILTER_ID' => $filterUser['FILTER_ID']))
            ->exec()
            ->fetch();

        if ($filterUserResult['cnt'] <= 0) {
            Model\SubscribeTable::delete(array('ID' => $filterUser['FILTER_ID']));
        }

        return $result;
    }

    /**
     * Remove from filter unnecessary parameters
     *
     * @param array $filter
     * @return array
     */
    public function normalizeFilter(array $filter)
    {
        foreach ($filter as $key => $value) {
            if (!preg_match('/PROPERTY|CATALOG_PRICE|OFFERS/i', $key)) {
                unset($filter[$key]);
            }
        }

        ksort($filter);
        $filter = serialize($filter);

        return $filter;
    }

    /**
     * Check array
     *
     * @param array $reseult
     * @return bool
     */
    public function isEmptyResult($reseult)
    {
        return (!is_array($reseult) || sizeof($reseult) <= 0);
    }
}