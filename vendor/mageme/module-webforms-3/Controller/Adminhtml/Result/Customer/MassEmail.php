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

namespace MageMe\WebForms\Controller\Adminhtml\Result\Customer;


use MageMe\WebForms\Model\Result;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;

class MassEmail extends AbstractAjaxCustomerMassAction
{
    const ACTION = 'email';

    /**
     * @inheritdoc
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function action(AbstractDb $collection): Phrase
    {
        $contact   = false;
        $recipient = 'admin';
        $email     = $this->getRequest()->getParam('input');
        if ($email) {
            $contact   = [
                'name' => $email,
                'email' => $email
            ];
            $recipient = 'contact';
        }

        /** @var Result $result */
        foreach ($collection as $result) {
            $this->repository->getById($result->getId())->sendEmail($recipient, $contact);
        }

        return __('A total of %1 record(s) have been emailed.', $collection->getSize());
    }
}