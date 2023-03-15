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

namespace MageMe\WebForms\Block\Result;

/**
 *
 */
class Info extends View
{
    /**
     * @var string
     */
    protected $_template = 'result/info.phtml';

    /**
     * @return string
     */
    public function getFormattedDate(): string
    {
        $result = $this->getResult();
        return $this->dateHelper->formatDate(
            $result->getCreatedAt(), $result->getStoreId()
        );
    }

}
