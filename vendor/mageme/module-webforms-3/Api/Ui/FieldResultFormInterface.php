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


use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Model\Result;

interface FieldResultFormInterface
{
    /**
     * Get config for result admin form
     *
     * @param null|ResultInterface|Result $result
     * @return array
     */
    public function getResultAdminFormConfig(ResultInterface $result = null): array;
}