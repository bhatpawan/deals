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

namespace MageMe\WebForms\Model\ResourceModel\Logic;


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\LogicInterface;
use MageMe\WebForms\Model\Logic;
use MageMe\WebForms\Model\ResourceModel\AbstractSearchResult;
use MageMe\WebForms\Model\ResourceModel\Field as FieldResource;
use MageMe\WebForms\Model\ResourceModel\Logic as LogicResource;

/**
 * Class Collection
 * @package MageMe\WebForms\Model\ResourceModel\Logic
 */
class Collection extends AbstractSearchResult
{
    /**
     * @inheritDoc
     */
    public function addFieldToFilter($field, $condition = null)
    {
        $isFormFilter = false;
        if (is_array($field)) {
            foreach ($field as &$value) {
                if ($value == LogicInterface::IS_ACTIVE) {
                    $value = 'main_table.' . LogicInterface::IS_ACTIVE;
                }
                if ($value == FieldInterface::FORM_ID) {
                    $isFormFilter = true;
                }
            }
        } else {
            if ($field == FieldInterface::FORM_ID) {
                $isFormFilter = true;
            }
            if ($field == LogicInterface::IS_ACTIVE) {
                $field = 'main_table.' . LogicInterface::IS_ACTIVE;
            }
        }
        if ($isFormFilter) {
            $this->join(
                ['fields' => $this->getTable(FieldResource::DB_TABLE)],
                'main_table.' . LogicInterface::FIELD_ID . ' = fields.' . FieldInterface::ID,
                [
                    FieldInterface::NAME,
                    FieldInterface::FORM_ID,
                ]
            );
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(Logic::class, LogicResource::class);
    }
}