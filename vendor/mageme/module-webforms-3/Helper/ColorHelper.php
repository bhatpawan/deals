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


class ColorHelper
{
    /**
     * @param string|null $color
     * @return bool
     */
    public function isHexColor(?string $color): bool
    {
        return (bool)preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', (string)$color);
    }
}