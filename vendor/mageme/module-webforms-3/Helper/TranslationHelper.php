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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class TranslationHelper
{
    const PATH_USE_TRANSLATION = 'webforms/general/use_translation';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Apply Magento csv and inline translation
     *
     * @param string|null $str
     * @return string|null
     */
    public function applyTranslation(?string $str): ?string
    {
        return $this->scopeConfig->getValue(self::PATH_USE_TRANSLATION, ScopeInterface::SCOPE_STORE) ?
            __($str) : $str;
    }
}