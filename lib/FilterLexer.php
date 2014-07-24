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

use Bitrix\Iblock;
use Bitrix\Main\Entity;

class FilterLexer
{
    /**
     * @var array
     */
    private $propertyList = array();

    /**
     * @var array
     */
    private $priceTypeList = array();

    /**
     * @var array
     */
    private $valueList = array();

    /**
     * @var array
     */
    protected $filterList = array();

    /**
     * @var array
     */
    protected $pattern = array(
        'property' => '/^.*(PROPERTY_(\d+))$/i',
        'price' => '/^.*(CATALOG_PRICE_(\d+))$/i'
    );

    /**
     * @param mixed $filter
     * @param bool $isSerialize
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addFilter($filter, $isSerialize = false) {
        if ($isSerialize && ($filter = @unserialize($filter)) === false) {
            throw new \InvalidArgumentException('Filter must be serialize array');
        } elseif (!is_array($filter)) {
            throw new \InvalidArgumentException('Filter must be array');
        }

        $this->filterList[$this->getFilterUniqId($filter)] = $filter;

        return $this;
    }

    /**
     * @return array
     */
    public function getFilter()
    {
        return $this->filterList;
    }

    /**
     * Parses the filter, we describe the properties of the
     * filter type and description price
     *
     * @return $this
     */
    public function parse()
    {
        if (sizeof($this->filterList) <= 0) {
            return array();
        }

        $property = $price = array();
        foreach ($this->filterList as $key => $items) {
            foreach ($items as $filterQuery => $filterValue) {
                if (preg_match($this->pattern['property'], $filterQuery, $match)) {
                    $property[] = $match[2];
                } elseif (preg_match($this->pattern['price'], $filterQuery, $match)) {
                    $price[] = $match[2];
                }

                if (is_array($match)) {
                    unset($this->filterList[$key][$filterQuery]);
                    $this->filterList[$key][$match[1]] = $filterValue;
                }
            }
        }

        if (sizeof($property)) {
            $queryBuilder = new Entity\Query(Iblock\PropertyTable::getEntity());
            $propertyResult = $queryBuilder->setSelect(array('*'))
                ->setFilter(array('ID' => $property))
                ->exec();

            while ($property = $propertyResult->fetch()) {
                $key = sprintf('PROPERTY_%d', $property['ID']);
                $this->propertyList[$key] = $property;
            }
        }

        if (sizeof($price)) {
            $priceTypeResult = \CCatalogGroup::GetList(
                array('SORT' => 'ASC'),
                array('ID' => $price)
            );

            while ($price = $priceTypeResult->Fetch()) {
                $key = sprintf('CATALOG_PRICE_%d', $price['ID']);
                $this->priceTypeList[$key] = $price;
            }
        }

        $this->getValueList();

        return $this;
    }

    /**
     * Gets the values ​​of the binding elements, sections and lists
     */
    protected function getValueList()
    {
        $section = $element = $list = array();
        foreach ($this->filterList as $uniq => $items) {
            foreach ($items as $filterQuery => $filterValue) {
                if ($this->propertyList[$filterQuery]['PROPERTY_TYPE'] == 'E') {
                    $this->addArrayToLink($element, (array)$filterValue);
                } elseif ($this->propertyList[$filterQuery]['PROPERTY_TYPE'] == 'G') {
                    $this->addArrayToLink($section, (array)$filterValue);
                } elseif ($this->propertyList[$filterQuery]['PROPERTY_TYPE'] == 'L') {
                    $this->addArrayToLink($list, (array)$filterValue);
                }
            }
        }

        $this->valueList['SECTION'] = $this->getValueSection($section);
        $this->valueList['ELEMENT'] = $this->getValueElement($element);
        $this->valueList['ENUM'] = $this->getValueEnum($list);
    }

    /**
     * Gets the value of section
     *
     * @param array $id
     * @return array
     */
    protected function getValueSection($id)
    {
        if (sizeof($id) <= 0) {
            return array();
        }

        $result = array();
        $queryBuilder = new Entity\Query(Iblock\SectionTable::getEntity());
        $queryBuilder->setSelect(array('*'))->setFilter(array('ID' => $iblockList));
        $sectionResult = $queryBuilder->exec();
        while ($section = $sectionResult->fetch()) {
            $result[$section['ID']] = $section;
        }

        return $result;
    }

    /**
     * Gets the value of element
     *
     * @param array $id
     * @return array
     */
    protected function getValueElement($id)
    {
        if (sizeof($id) <= 0) {
            return array();
        }

        $result = array();
        $queryBuilder = new Entity\Query(Iblock\ElementTable::getEntity());
        $queryBuilder->setSelect(array('*'))->setFilter(array('ID' => $id));
        $elementResult = $queryBuilder->exec();
        while ($element = $elementResult->fetch()) {
            $result[$element['ID']] = $element;
        }

        return $result;
    }

    /**
     * Gets the value of enum
     *
     * @param array $id
     * @return array
     */
    protected function getValueEnum($id)
    {
        if (sizeof($id) <= 0) {
            return array();
        }

        $result = array();
        $queryBuilder = new Entity\Query(Iblock\PropertyEnumerationTable::getEntity());
        $queryBuilder->setSelect(array('*'))->setFilter(array('ID' => $id));
        $enumListResult = $queryBuilder->exec();
        while ($enum = $enumListResult->fetch()) {
            $result[$enum['ID']] = $enum;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getValue()
    {
        return $this->valueList;
    }

    /**
     * @return array
     */
    public function getProperty()
    {
        return $this->propertyList;
    }

    /**
     * @return array
     */
    public function getPriceType()
    {
        return $this->priceTypeList;
    }

    /**
     * @param array $array
     * @param array $dataList
     * @return void
     */
    protected function addArrayToLink(&$array, $dataList) {
        foreach ($dataList as $value) {
            array_push($array, $value);
        }
    }

    /**
     * @param array $filter
     * @return string
     */
    public function getFilterUniqId(array $filter) {
        return hash('md5', trim(serialize($filter)));
    }
}