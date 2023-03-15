<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to a newer
 * version in the future.
 *
 * Copyright (c) MageMe (https://mageme.com)
 **/

namespace MageMe\WebForms\Model\ResourceModel\Form;


use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Model\Form;
use MageMe\WebForms\Model\ResourceModel\AbstractSearchResult;
use MageMe\WebForms\Model\ResourceModel\Form as FormResource;
use MageMe\WebForms\Model\ResourceModel\Result;

/**
 * Form collection
 *
 */
class Collection extends AbstractSearchResult
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(Form::class, FormResource::class);
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {
        $lastResultDateColumn = sprintf(
            '(SELECT %s.%s FROM %s WHERE %s.%s = %s.%s ORDER BY %s.%s DESC limit 1)',
            $this->getTable(Result::DB_TABLE),
            ResultInterface::CREATED_AT,
            $this->getTable(Result::DB_TABLE),
            $this->getTable(Result::DB_TABLE),
            ResultInterface::FORM_ID,
            'main_table',
            FormInterface::ID,
            $this->getTable(Result::DB_TABLE),
            ResultInterface::CREATED_AT
        );
        $this->getSelect()->from(['main_table' => $this->getMainTable()],
            [
                '*',
                'last_result_date' => $lastResultDateColumn
            ]
        );
        $this->addFilterToMap('last_result_date', $lastResultDateColumn);

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _translateCondition($field, $condition): string
    {
        if ($field == 'last_result_date') {
            $field = $this->_getMappedField($field);
            return $this->_getConditionSql($field, $condition);
        }
        return parent::_translateCondition($field, $condition);
    }
}
