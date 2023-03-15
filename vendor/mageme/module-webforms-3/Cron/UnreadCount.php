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

namespace MageMe\WebForms\Cron;

use MageMe\WebForms\Helper\StatisticsHelper;

class UnreadCount
{

    /**
     * @var StatisticsHelper
     */
    private $statisticsHelper;

    public function __construct(
        StatisticsHelper $statisticsHelper
    )
    {
        $this->statisticsHelper = $statisticsHelper;
    }

    public function execute()
    {
        if ($this->statisticsHelper->getUnreadCron()) {
            $this->statisticsHelper->calculateUnreadCount();
        }
    }

}