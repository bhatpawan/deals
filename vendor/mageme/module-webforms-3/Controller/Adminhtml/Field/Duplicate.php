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

namespace MageMe\WebForms\Controller\Adminhtml\Field;

use Exception;
use MageMe\WebForms\Api\Data\FieldInterface;
use Magento\Framework\Controller\ResultFactory;

class Duplicate extends AbstractFieldAction
{
    const REDIRECT_URL = 'webforms/field/edit';

    /**
     * @var array
     */
    protected $redirect_params = [];

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $id             = $this->getRequest()->getParam(FieldInterface::ID);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $item = $this->repository->getById($id)->duplicate();
            $this->messageManager->addSuccessMessage(
                __('The field has been been duplicated.')
            );
            $this->redirect_params = [FieldInterface::ID => $item->getId()];
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('webforms/form');
        }
        return $resultRedirect->setPath(static::REDIRECT_URL, $this->redirect_params);
    }
}
