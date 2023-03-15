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


class CssHelper
{
    /**
     * Get responsive CSS tags
     *
     * @param string|null $lgWidth
     * @param string|null $mdWidth
     * @param string|null $smWidth
     * @param bool|null $isRowLg
     * @param bool|null $isRowMd
     * @param bool|null $isRowSm
     * @return string
     */
    public function getResponsiveCss(
        ?string $lgWidth,
        ?string $mdWidth,
        ?string $smWidth,
        ?bool   $isRowLg,
        ?bool   $isRowMd,
        ?bool   $isRowSm
    ): string
    {
        $prefix = "wf-";
        $class  = [];

        if ($lgWidth) {
            $class [] = $prefix . "lg-" . $lgWidth;
        }
        if ($mdWidth) {
            $class [] = $prefix . "md-" . $mdWidth;
        }
        if ($smWidth) {
            $class [] = $prefix . "sm-" . $smWidth;
        }

        if ($isRowLg) {
            $class [] = $prefix . "lg-row";
        }
        if ($isRowMd) {
            $class [] = $prefix . "md-row";
        }
        if ($isRowSm) {
            $class [] = $prefix . "sm-row";
        }
        return implode(" ", $class);
    }
}