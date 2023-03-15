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

namespace MageMe\WebForms\Controller\Adminhtml\Fieldset;


use MageMe\WebForms\Api\Data\FieldsetInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;

class AjaxMassWidthLg extends AbstractAjaxFieldsetMassAction
{
    const ACTION = 'update';

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    protected function action(AbstractDb $collection): Phrase
    {
        $newValue = $this->getRequest()->getParam(FieldsetInterface::WIDTH_PROPORTION_LG);
        foreach ($collection as $item) {
            $model = $this->repository->getById($item->getId());
            $model->setWidthProportionLg($newValue);
            $this->repository->save($model);
        }
        return __('A total of %1 record(s) have been updated.', $collection->getSize());
    }
}