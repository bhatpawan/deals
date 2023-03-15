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


class FileHelper
{
    /**
     * @param string $fileName
     * @return string
     */
    public function getShortFilename(string $fileName): string
    {
        if (strlen($fileName) < 30) return filter_var($fileName, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $nameStart = mb_substr($fileName, 0, 15);
        $nameEnd   = mb_substr($fileName, -7);
        return filter_var($nameStart . '...' . $nameEnd, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
}
