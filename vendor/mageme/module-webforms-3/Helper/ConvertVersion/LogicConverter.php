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

namespace MageMe\WebForms\Helper\ConvertVersion;


use MageMe\WebForms\Api\Data\LogicInterface;
use MageMe\WebForms\Api\LogicRepositoryInterface;
use MageMe\WebForms\Model\LogicFactory;
use MageMe\WebForms\Setup\Table\LogicTable;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class LogicConverter
{
    /**#@+
     * V2 constants
     */
    const ID = 'id';
    const FIELD_ID = 'field_id';
    const LOGIC_CONDITION = 'logic_condition';
    const ACTION = 'action';
    const AGGREGATION = 'aggregation';
    const VALUE_SERIALIZED = 'value_serialized';
    const TARGET_SERIALIZED = 'target_serialized';
    const CREATED_TIME = 'created_time';
    const UPDATE_TIME = 'update_time';
    const IS_ACTIVE = 'is_active';

    const VALUE = 'value';
    const TARGET = 'target';
    /**#@-*/

    const TABLE_LOGIC = 'webforms_logic';
    /**
     * @var LogicFactory
     */
    private $logicFactory;

    /**
     * @var LogicRepositoryInterface
     */
    private $logicRepository;

    /**
     * LogicConverter constructor.
     * @param LogicRepositoryInterface $logicRepository
     * @param LogicFactory $logicFactory
     */
    public function __construct(
        LogicRepositoryInterface $logicRepository,
        LogicFactory             $logicFactory
    )
    {
        $this->logicFactory    = $logicFactory;
        $this->logicRepository = $logicRepository;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws CouldNotSaveException
     */
    public function convert(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $select     = $connection->select()->from($setup->getTable(self::TABLE_LOGIC));
        $query      = $select->query()->fetchAll();
        foreach ($query as $oldData) {
            $connection->insertOnDuplicate($setup->getTable(LogicTable::TABLE_NAME), [
                LogicInterface::ID => $oldData[self::ID],
                LogicInterface::FIELD_ID => $oldData[self::FIELD_ID]
            ]);
            $logic = $this->logicFactory->create();
            $logic->setData($this->convertV2Data($oldData));
            $this->logicRepository->save($logic);
        }
    }

    /**
     * @param array $oldData
     * @return array
     */
    public function convertV2Data(array $oldData): array
    {
        $value  = $this->convertV2SerializedArray($oldData, self::VALUE, self::VALUE_SERIALIZED);
        $target = $this->convertV2SerializedArray($oldData, self::TARGET, self::TARGET_SERIALIZED);
        return [
            LogicInterface::ID => $oldData[self::ID] ?? null,
            LogicInterface::FIELD_ID => $oldData[self::FIELD_ID] ?? null,
            LogicInterface::LOGIC_CONDITION => $oldData[self::LOGIC_CONDITION] ?? null,
            LogicInterface::ACTION => $oldData[self::ACTION] ?? null,
            LogicInterface::AGGREGATION => $oldData[self::AGGREGATION] ?? null,
            LogicInterface::IS_ACTIVE => $oldData[self::IS_ACTIVE] ?? null,
            LogicInterface::VALUE_SERIALIZED => $oldData[self::VALUE_SERIALIZED] ?? null,
            LogicInterface::TARGET_SERIALIZED => $oldData[self::TARGET_SERIALIZED] ?? null,
            LogicInterface::CREATED_AT => $oldData[self::CREATED_TIME] ?? null,
            LogicInterface::UPDATED_AT => $oldData[self::UPDATE_TIME] ?? null,

            LogicInterface::VALUE => $value,
            LogicInterface::TARGET => $target
        ];
    }

    /**
     * @param array $oldData
     * @param string $field
     * @param string $serializedField
     * @return array
     */
    protected function convertV2SerializedArray(array $oldData, string $field, string $serializedField): ?array
    {
        $value = isset($oldData[$serializedField]) ? unserialize($oldData[$serializedField]) : null;
        if (!$value) {
            $value = (isset($oldData[$field]) && is_array($oldData[$field])) ? $oldData[$field] : [];
        }
        return $value;
    }

    /**
     * Convert V2 store data
     *
     * @param array $storeData
     * @return array
     */
    public function convertV2StoreData(array $storeData): array
    {
        $newData = [];
        foreach ($this->convertV2Data($storeData) as $key => $value) {
            if (!is_null($value)) {
                $newData[$key] = $value;
            }
        }
        $defaults = [
            LogicInterface::VALUE,
            LogicInterface::TARGET
        ];
        foreach ($defaults as $default) {
            if (!isset($storeData[$default])) {
                unset($newData[$default]);
            }
        }
        return $newData;
    }
}
