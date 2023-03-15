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

namespace MageMe\WebForms\Block\Adminhtml\Logic\Edit\Button;


use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Block\Adminhtml\Common\Button\SaveContainer;

class Save extends SaveContainer
{
    /**
     * @inheritdoc
     */
    protected $target = 'webforms_logic_form.webforms_logic_form';

    /**
     * @inheritdoc
     */
    protected function getParams(bool $redirect): array
    {
        $params = parent::getParams($redirect);
        $formId = $this->request->getParam(FormInterface::ID);
        if ($formId) {
            $params[] = [FormInterface::ID => $formId];
        }
        return $params;
    }
}