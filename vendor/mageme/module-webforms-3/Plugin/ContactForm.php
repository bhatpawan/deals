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

namespace MageMe\WebForms\Plugin;

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Block\Form;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

class ContactForm
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * ContactForm constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Contact\Block\ContactForm $contactForm
     * @throws LocalizedException
     */
    public function beforeToHtml(\Magento\Contact\Block\ContactForm $contactForm)
    {
        if ($this->scopeConfig->getValue('webforms/contacts/enable', ScopeInterface::SCOPE_STORE)) {

            $contactForm->setTemplate('MageMe_WebForms::contact/form.phtml');

            $block = $contactForm->getLayout()->createBlock(Form::class, 'webforms.contact.form', [
                'data' => [
                    FormInterface::ID => $this->scopeConfig->getValue('webforms/contacts/webform',
                        ScopeInterface::SCOPE_STORE)
                ]
            ]);
            $contactForm->append($block);
        }
    }
}
