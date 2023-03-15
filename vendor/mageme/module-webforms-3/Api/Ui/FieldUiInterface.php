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

namespace MageMe\WebForms\Api\Ui;


use MageMe\WebForms\Api\Data\FieldInterface;

interface FieldUiInterface
{
    /**
     * Get meta for render fields on Admin UI form
     *
     * @param string $prefix
     * @return array
     */
    public function getUiMeta(string $prefix = ''): array;

    /**
     * @return FieldInterface
     */
    public function getField(): FieldInterface;

    /**
     * @param FieldInterface $field
     * @return $this
     */
    public function setField(FieldInterface $field): FieldUiInterface;

    /**
     * Get meta for value at logic form
     *
     * @return array
     */
    public function getLogicValueMeta(): array;
}
