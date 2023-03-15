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


use MageMe\WebForms\Api\Data\StoreInterface;
use MageMe\WebForms\Api\StoreRepositoryInterface;
use MageMe\WebForms\Model\StoreFactory;
use MageMe\WebForms\Setup\Table\StoreTable;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class StoreConverter
{
    /**#@+
     * V2 constants
     */
    const ID = 'id';
    const STORE_ID = 'store_id';
    const ENTITY_TYPE = 'entity_type';
    const ENTITY_ID = 'entity_id';
    const STORE_DATA = 'store_data';
    /**#@-*/

    const TABLE_STORE = 'webforms_store';

    /**
     * @var StoreFactory
     */
    private $storeFactory;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * StoreConverter constructor.
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreFactory $storeFactory
     */
    public function __construct(
        StoreRepositoryInterface $storeRepository,
        StoreFactory             $storeFactory
    )
    {
        $this->storeFactory    = $storeFactory;
        $this->storeRepository = $storeRepository;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws CouldNotSaveException
     */
    public function convert(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $select     = $connection->select()->from($setup->getTable(self::TABLE_STORE));
        $query      = $select->query()->fetchAll();
        foreach ($query as $oldData) {
            $connection->insertOnDuplicate($setup->getTable(StoreTable::TABLE_NAME), [
                StoreInterface::ID => $oldData[self::ID],
                StoreInterface::STORE_ID => $oldData[self::STORE_ID],
                StoreInterface::ENTITY_ID => $oldData[self::ENTITY_ID]
            ]);
            $store = $this->storeFactory->create();
            $store->setData($this->convertV2Data($oldData));
            $this->storeRepository->save($store);
        }
    }

    /**
     * @param array $oldData
     * @return array
     */
    public function convertV2Data(array $oldData): array
    {
        $storeData = isset($oldData[self::STORE_DATA]) ? unserialize($oldData[self::STORE_DATA]) : null;
        return [
            StoreInterface::ID => $oldData[self::ID] ?? null,
            StoreInterface::STORE_ID => $oldData[self::STORE_ID] ?? null,
            StoreInterface::ENTITY_ID => $oldData[self::ENTITY_ID] ?? null,
            StoreInterface::ENTITY_TYPE => $oldData[self::ENTITY_TYPE] ?? null,
            StoreInterface::STORE_DATA_SERIALIZED => $oldData[self::STORE_DATA] ?? null,

            StoreInterface::STORE_DATA => $storeData ? $storeData : []
        ];
    }
}
