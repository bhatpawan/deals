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


use MageMe\WebForms\Helper\ConvertVersion\FieldConverter;
use MageMe\WebForms\Helper\ConvertVersion\FieldsetConverter;
use MageMe\WebForms\Helper\ConvertVersion\FileDropzoneConverter;
use MageMe\WebForms\Helper\ConvertVersion\FormConverter;
use MageMe\WebForms\Helper\ConvertVersion\LogicConverter;
use MageMe\WebForms\Helper\ConvertVersion\MessageConverter;
use MageMe\WebForms\Helper\ConvertVersion\QuickresponseConverter;
use MageMe\WebForms\Helper\ConvertVersion\ResultConverter;
use MageMe\WebForms\Helper\ConvertVersion\ResultValueConverter;
use MageMe\WebForms\Helper\ConvertVersion\StoreConverter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class ConvertVersionHelper
{
    const TABLE_CORE_CONFIG_DATA = 'core_config_data';
    const FIELD_CORE_CONFIG_DATA_CONFIG_ID = 'config_id';
    const FIELD_CORE_CONFIG_DATA_SCOPE = 'scope';
    const FIELD_CORE_CONFIG_DATA_SCOPE_ID = 'scope_id';
    const FIELD_CORE_CONFIG_DATA_PATH = 'path';
    const FIELD_CORE_CONFIG_DATA_VALUE = 'value';
    const FIELD_CORE_CONFIG_DATA_UPDATED_AT = 'updated_at';
    /**
     * @var FieldConverter
     */
    private $fieldConverter;
    /**
     * @var FieldsetConverter
     */
    private $fieldsetConverter;
    /**
     * @var FileDropzoneConverter
     */
    private $fileDropzoneConverter;
    /**
     * @var FormConverter
     */
    private $formConverter;
    /**
     * @var LogicConverter
     */
    private $logicConverter;
    /**
     * @var MessageConverter
     */
    private $messageConverter;
    /**
     * @var QuickresponseConverter
     */
    private $quickresponseConverter;
    /**
     * @var ResultConverter
     */
    private $resultConverter;
    /**
     * @var ResultValueConverter
     */
    private $resultValueConverter;
    /**
     * @var StoreConverter
     */
    private $storeConverter;

    public function __construct(
        FieldConverter         $fieldConverter,
        FieldsetConverter      $fieldsetConverter,
        FileDropzoneConverter  $fileDropzoneConverter,
        FormConverter          $formConverter,
        LogicConverter         $logicConverter,
        MessageConverter       $messageConverter,
        QuickresponseConverter $quickresponseConverter,
        ResultConverter        $resultConverter,
        ResultValueConverter   $resultValueConverter,
        StoreConverter         $storeConverter
    )
    {
        $this->fieldConverter         = $fieldConverter;
        $this->fieldsetConverter      = $fieldsetConverter;
        $this->fileDropzoneConverter  = $fileDropzoneConverter;
        $this->formConverter          = $formConverter;
        $this->logicConverter         = $logicConverter;
        $this->messageConverter       = $messageConverter;
        $this->quickresponseConverter = $quickresponseConverter;
        $this->resultConverter        = $resultConverter;
        $this->resultValueConverter   = $resultValueConverter;
        $this->storeConverter         = $storeConverter;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function convertDataToV3(ModuleDataSetupInterface $setup)
    {
        $this->formConverter->convert($setup);
        $this->fieldsetConverter->convert($setup);
        $this->fieldConverter->convert($setup);
        $this->logicConverter->convert($setup);
        $this->resultConverter->convert($setup);
        $this->resultValueConverter->convert($setup);
        $this->messageConverter->convert($setup);
        $this->storeConverter->convert($setup);
        $this->quickresponseConverter->convert($setup);
        $this->fileDropzoneConverter->convert($setup);
        $this->convertConfigData($setup);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    protected function convertConfigData(ModuleDataSetupInterface $setup)
    {
        $this->updateConfig(
            $setup,
            'webforms/email/email_reply_to',
            'webforms/email/customer_notification_reply_to'
        );
        $this->updateConfig(
            $setup,
            'webforms/gdpr/purge_enable',
            'webforms/gdpr/is_purge_enabled'
        );
        $this->updateConfig(
            $setup,
            'webforms/gdpr/collect_customer_ip',
            'webforms/general/collect_customer_ip'
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param string $oldPath
     * @param string $newPath
     */
    protected function updateConfig(ModuleDataSetupInterface $setup, string $oldPath, string $newPath)
    {
        foreach ($this->getConfigArrayByPath($setup, $oldPath) as $item) {
            $this->insertOrUpdateConfig($setup,
                $newPath,
                $item[self::FIELD_CORE_CONFIG_DATA_VALUE],
                $item[self::FIELD_CORE_CONFIG_DATA_SCOPE],
                $item[self::FIELD_CORE_CONFIG_DATA_SCOPE_ID]
            );
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param string $path
     * @return array
     */
    protected function getConfigArrayByPath(ModuleDataSetupInterface $setup, string $path): array
    {
        $connection = $setup->getConnection();
        $select = $connection->select()->from($setup->getTable(self::TABLE_CORE_CONFIG_DATA))
            ->where(
                self::FIELD_CORE_CONFIG_DATA_PATH . ' = ?',
                $path
            );
        return $select->query()->fetchAll();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param string $path
     * @param mixed $value
     * @param string $scope
     * @param int $scopeId
     */
    protected function insertOrUpdateConfig(ModuleDataSetupInterface $setup, string $path, $value, string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, int $scopeId = 0)
    {
        $connection = $setup->getConnection();
        $configData = $this->getConfigForUpdate($setup, $path, $scope, $scopeId);
        if ($configData) {
            $connection->update(
                $setup->getTable(self::TABLE_CORE_CONFIG_DATA),
                [self::FIELD_CORE_CONFIG_DATA_PATH => $path],
                [
                    self::FIELD_CORE_CONFIG_DATA_CONFIG_ID . ' = ?' => $configData[self::FIELD_CORE_CONFIG_DATA_CONFIG_ID]
                ]);
        } else {
            $connection->insert($setup->getTable(self::TABLE_CORE_CONFIG_DATA), [
                self::FIELD_CORE_CONFIG_DATA_PATH => $path,
                self::FIELD_CORE_CONFIG_DATA_VALUE => $value,
                self::FIELD_CORE_CONFIG_DATA_SCOPE => $scope,
                self::FIELD_CORE_CONFIG_DATA_SCOPE_ID => $scopeId
            ]);
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     * @return mixed
     */
    protected function getConfigForUpdate(ModuleDataSetupInterface $setup, string $path, string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, int $scopeId = 0)
    {
        $connection = $setup->getConnection();
        $select = $connection->select()->from($setup->getTable(self::TABLE_CORE_CONFIG_DATA))
            ->where(
                self::FIELD_CORE_CONFIG_DATA_PATH . ' = ?',
                $path
            )
            ->where(
                self::FIELD_CORE_CONFIG_DATA_SCOPE . ' = ?',
                $scope
            )
            ->where(
                self::FIELD_CORE_CONFIG_DATA_SCOPE_ID . ' = ?',
                $scopeId
            );
        return $connection->fetchRow($select);
    }
}