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

namespace MageMe\WebForms\Controller\Adminhtml\Result;


use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;

class AjaxIsReplied extends AbstractAjaxResultMassAction
{
    /**
     * @inheritdoc
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::reply';
    const ACTION = 'update';

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    protected function action(AbstractDb $collection): Phrase
    {

        $newStatus = (bool)$this->getRequest()->getParam('status');
        foreach ($collection as $item) {
            $model = $this->repository->getById($item->getId());
            $model->setIsReplied($newStatus);
            if($newStatus) $model->setIsRead(true);
            $this->repository->save($model);
        }
        return __('A total of %1 record(s) have been updated.', $collection->getSize());
    }
}
