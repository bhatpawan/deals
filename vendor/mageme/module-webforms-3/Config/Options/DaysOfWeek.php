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

namespace MageMe\WebForms\Config\Options;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Store\Model\ScopeInterface;

class DaysOfWeek implements OptionSourceInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * DaysOfWeek constructor.
     * @param ResolverInterface $localeResolver
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ResolverInterface    $localeResolver,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig    = $scopeConfig;
        $this->localeResolver = $localeResolver;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        $firstDay = (int)$this->scopeConfig->getValue(
            'general/locale/firstday',
            ScopeInterface::SCOPE_STORE
        );
        $days     = (new DataBundle())
            ->get($this->localeResolver->getLocale())['calendar']['gregorian']['dayNames']['format']['wide'] ?: [];
        $l        = [];
        $r        = [];
        foreach ($days as $code => $name) {
            $item = ['label' => $name, 'value' => (string)$code];
            if ($code < $firstDay) {
                $r[] = $item;
            } else {
                $l[] = $item;
            }
        }
        return array_merge($l, $r);
    }
}