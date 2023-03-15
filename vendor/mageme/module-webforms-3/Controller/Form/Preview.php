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

namespace MageMe\WebForms\Controller\Form;


use Exception;

class Preview extends View
{
    const IS_FORM_PREVIEW = 'is_form_preview';

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function execute()
    {
        $this->registry->register(self::IS_FORM_PREVIEW, true);
        return parent::execute()->setHeader('X-Robots-Tag', 'noindex');
    }
}
