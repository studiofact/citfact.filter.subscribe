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
     * @param array $fileds
     * @return int|bool
     * @throws \InvalidArgumentException
     */
    public function addFilter(array $fileds)
    {
        $fitler = $fileds['FILTER'];
        if (!is_array($fitler) || sizeof($fitler) <= 0) {
            throw new \InvalidArgumentException('Filter can not be empty');
        }

        $filter = ksort($filter);
        $filter = serialize($fitler);

        $iblockId = (int)$fileds['IBLOCK_ID'];
        $sectionId = (int)$fileds['SECTION_ID'];
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
            return $subscribe['ID'];
        }

        $subscribeResult = Model\SubscribeTable::add(array(
            'FILTER' => $filter,
            'IBLOCK_ID' => $iblockDataResult['ID'],
            'SECTION_ID' => isset($sectionDataResult) ? $sectionDataResult['ID'] : '',
        ));

        return ($subscribeResult->isSuccess())
            ? $subscribeResult->getId()
            : false;
    }

    /**
     * Save link user filter with filter
     * Requirement fields: USER_ID, FILTER_ID
     *
     * @param array $fields
     * @return int|bool
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
            return $filterUserData['ID'];
        }

        $filterUserResult = Model\SubscribeUserTable::add(array(
            'USER_ID' => $userData['ID'],
            'FILTER_ID' => $subscribe['ID']
        ));

        return ($filterUserResult->isSuccess())
            ? $filterUserResult->getId()
            : false;
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