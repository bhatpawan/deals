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

namespace MageMe\WebForms\Model\ResourceModel\Result\Customer;


use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Model\ResourceModel\Form as FormResource;
use MageMe\WebForms\Model\ResourceModel\Result as ResultResource;
use MageMe\WebForms\Model\Result;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    /**
     * @inheritDoc
     */
    public function _initSelect()
    {
        parent::_initSelect();
        $collection = $this->getSelect()
            ->joinLeft(
                ['webforms' => $this->getTable(FormResource::DB_TABLE)],
                'main_table.' . ResultInterface::FORM_ID . ' = webforms.' . FormInterface::ID,
                ['form' => FormInterface::NAME]
            );
        $this->addFilterToMap(ResultInterface::ID, 'main_table.' . ResultInterface::ID);
        $this->addFilterToMap('form', 'webforms.' . FormInterface::NAME);
        $this->addFilterToMap(ResultInterface::CUSTOMER_ID, 'main_table.' . ResultInterface::CUSTOMER_ID);
        $this->addFilterToMap(ResultInterface::FORM_ID, 'main_table.' . ResultInterface::FORM_ID);

        return $collection;
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(Result::class, ResultResource::class);
    }

    /**
     * @inheritDoc
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        foreach ($this as $item) {
            $this->_resource->deserializeFieldsFromJSON($item);
        }
        return $this;
    }
}
