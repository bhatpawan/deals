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

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Setup\Table\FormTable;
use MageMe\WebForms\Setup\Table\ResultTable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;

class StatisticsHelper
{
    const PATH_UNREAD_ENABLE = 'webforms/general/unread_enable';
    const PATH_UNREAD_CRON = 'webforms/general/unread_cron';
    const TOTAL_UNREAD_COUNT = 'total_unread_count';
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    public function __construct(
        ResourceConnection   $resourceConnection,
        ScopeConfigInterface $config
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->config             = $config;

    }

    public function calculateUnreadCount()
    {
        $connection = $this->getConnection();
        $sql        = "UPDATE " . $this->resourceConnection->getTableName(FormTable::TABLE_NAME) . "  f
            SET " . FormInterface::UNREAD_COUNT . " = (
            SELECT COUNT(" . ResultInterface::FORM_ID . ") FROM " . $this->resourceConnection->getTableName(ResultTable::TABLE_NAME) . " r
            WHERE r." . ResultInterface::FORM_ID . " = f." . FormInterface::ID . " AND r." . ResultInterface::IS_READ . " < 1)";
        $connection->query($sql);
    }

    private function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }

    public function getTotalUnreadCount(): int
    {
        $connection = $this->getConnection();
        $sql        = "SELECT SUM(" . FormInterface::UNREAD_COUNT . ") as " . self::TOTAL_UNREAD_COUNT . "
        FROM " . $this->resourceConnection->getTableName(FormTable::TABLE_NAME);
        return (int)$connection->fetchRow($sql)[self::TOTAL_UNREAD_COUNT];
    }

    public function getUnreadEnable(): int
    {
        return (int)$this->config->getValue(self::PATH_UNREAD_ENABLE);
    }

    public function getUnreadCron(): int
    {
        return (int)$this->config->getValue(self::PATH_UNREAD_CRON);
    }
}
