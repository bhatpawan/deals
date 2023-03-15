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

namespace MageMe\WebForms\Helper;


use Magento\Framework\Exception\InputException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;

class SurveyHelper
{
    const COOKIE_NAME_PREFIX = 'WFS_';
    const COOKIE_PREFIX = 'webform_survey';

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;
    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;
    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * SurveyHelper constructor.
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        CookieManagerInterface  $cookieManager,
        CookieMetadataFactory   $cookieMetadataFactory,
        SessionManagerInterface $sessionManager
    )
    {
        $this->sessionManager        = $sessionManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager         = $cookieManager;
    }

    /**
     * @param int $formId
     * @return string|null
     */
    public function getCookie(int $formId): ?string
    {
        return $this->cookieManager->getCookie($this->getCookieName($formId));
    }

    /**
     * @param int $formId
     * @return string
     */
    protected function getCookieName(int $formId): string
    {
        return self::COOKIE_NAME_PREFIX . md5(self::COOKIE_PREFIX . $formId);
    }

    /**
     * @param int $formId
     * @param mixed $value
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     * @throws InputException
     */
    public function setCookie(int $formId, $value = 1)
    {
        $this->cookieManager->setPublicCookie(
            $this->getCookieName($formId),
            md5((string)$value),
            $this->getCookieMeta()
        );
    }

    /**
     * @return PublicCookieMetadata
     */
    protected function getCookieMeta(): PublicCookieMetadata
    {
        return $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration(2147483647)
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->sessionManager->getCookieDomain());
    }

    /**
     * @param int $formId
     * @throws FailureToSendException
     * @throws InputException
     */
    public function deleteCookie(int $formId)
    {
        $this->cookieManager->deleteCookie(
            $this->getCookieName($formId),
            $this->getCookieMeta()
        );
    }
}