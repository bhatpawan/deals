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

namespace MageMe\WebForms\Ui\Component\Result\Listing\Bookmark;

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Ui\Component\Common\Listing\AbstractUniqBookmark;
use Magento\Customer\Api\Data\CustomerInterface;

class CustomerBookmark extends AbstractUniqBookmark
{
    /**
     * @inheritDoc
     */
    protected function getNamespace(): string
    {
        $formId = $this->request->getParam(FormInterface::ID);
        if ($formId) {
            return 'webforms_customer_result_listing_f' . $formId;
        }
        return 'webforms_customer_result_listing' . $this->request->getParam(CustomerInterface::ID);
    }
}
