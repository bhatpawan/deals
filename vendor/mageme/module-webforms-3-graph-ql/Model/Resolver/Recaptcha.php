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

namespace MageMe\WebFormsGraphQl\Model\Resolver;

use MageMe\WebForms\Helper\CaptchaHelper;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\ScopeInterface;

class Recaptcha implements ResolverInterface
{
    /**
     * @var HttpRequest
     */
    private $request;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var Session
     */
    private $session;
    /**
     * @var CaptchaHelper
     */
    private $captchaHelper;

    /**
     * @param CaptchaHelper $captchaHelper
     * @param SessionFactory $sessionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param HttpRequest $request
     */
    public function __construct(
        CaptchaHelper        $captchaHelper,
        SessionFactory       $sessionFactory,
        ScopeConfigInterface $scopeConfig,
        HttpRequest          $request
    ) {
        $this->request       = $request;
        $this->scopeConfig   = $scopeConfig;
        $this->session       = $sessionFactory->create();
        $this->captchaHelper = $captchaHelper;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $response = (string)$this->request->getHeader('X-ReCaptcha');
        if (empty($response)
            || empty($this->captchaHelper->getPublicKey())
            || empty($this->captchaHelper->getPrivateKey())) {
            throw new GraphQlInputException(
                __($this->scopeConfig->getValue('webforms/captcha/technical_failure_message',
                    ScopeInterface::SCOPE_STORE))
            );
        }
        if (!$this->captchaHelper->verify($response)) {
            throw new GraphQlInputException(
                __($this->scopeConfig->getValue('webforms/captcha/validation_failure_message',
                    ScopeInterface::SCOPE_STORE))
            );
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->session->setData('captcha_verified', true);
        return true;
    }
}