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

namespace MageMe\WebForms\Ui\Component\Field\Listing;

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Ui\Component\Common\Listing\AbstractUniqBookmark;

class Bookmark extends AbstractUniqBookmark
{
    /**
     * @inheritDoc
     */
    protected function getNamespace(): string
    {
        return 'webforms_form_field_listing' . $this->request->getParam(FormInterface::ID);
    }
}
