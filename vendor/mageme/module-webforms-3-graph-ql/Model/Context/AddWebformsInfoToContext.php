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

namespace MageMe\WebFormsGraphQl\Model\Context;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\GraphQl\Model\Query\ContextParametersInterface;
use Magento\GraphQl\Model\Query\ContextParametersProcessorInterface;

class AddWebformsInfoToContext implements ContextParametersProcessorInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @param SessionFactory $sessionFactory
     */
    public function __construct(SessionFactory $sessionFactory)
    {
        $this->session = $sessionFactory->create();
    }

    /**
     * @inheritDoc
     */
    public function execute(ContextParametersInterface $contextParameters): ContextParametersInterface
    {
        $contextParameters->addExtensionAttribute('is_captcha_verified',
            (bool)$this->session->getData('captcha_verified'));
        return $contextParameters;
    }
}