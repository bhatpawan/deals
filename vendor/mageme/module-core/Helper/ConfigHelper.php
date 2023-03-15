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

namespace MageMe\Core\Helper;


use Magento\Framework\App\Area;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;

class ConfigHelper
{
    /**
     * @var State
     */
    protected $state;
    /**
     * @var ProductMetadataInterface
     */
    protected $metadata;

    public function __construct(
        ProductMetadataInterface $metadata,
        State                    $state
    )
    {
        $this->state    = $state;
        $this->metadata = $metadata;
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->isArea(Area::AREA_ADMINHTML);
    }

    /**
     * @param string $area
     * @return bool
     */
    protected function isArea(string $area): bool
    {
        try {
            return $this->state->getAreaCode() == $area;
        } catch (LocalizedException $e) {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isFrontend(): bool
    {
        return $this->isArea(Area::AREA_FRONTEND);
    }

    /**
     * @return string
     */
    public function getMagentoVersion(): string
    {
        return $this->metadata->getVersion();
    }
}
