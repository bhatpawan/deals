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

namespace MageMe\WebForms\Block\Form\Element\Script;

use MageMe\WebForms\Block\Form\Element\AbstractElement;
use Magento\Framework\Exception\NoSuchEntityException;

class Submit extends AbstractElement
{
    /**
     * @inheritdoc
     */
    protected $_template = self::TEMPLATE_PATH . 'script/submit.phtml';

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isAjax(): bool
    {
        return (bool)$this->_storeManager->getStore()->getConfig('webforms/general/ajax');
    }
}
