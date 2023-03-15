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


use MageMe\WebForms\Api\Data\ResultValueInterface;
use MageMe\WebForms\Api\ResultValueRepositoryInterface;
use MageMe\WebForms\Model\ResultValueFactory;
use MageMe\WebForms\Setup\Table\ResultValueTable;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class ResultValueConverter
{
    /**#@+
     * V2 constants
     */
    const ID = 'id';
    const RESULT_ID = 'result_id';
    const FIELD_ID = 'field_id';
    const VALUE = 'value';
    const KEY = 'key';
    /**#@-*/

    const TABLE_RESULT_VALUE = 'webforms_results_values';

    /**
     * @var ResultValueFactory
     */
    private $resultValueFactory;

    /**
     * @var ResultValueRepositoryInterface
     */
    private $resultValueRepository;

    /**
     * ResultValueConverter constructor.
     * @param ResultValueRepositoryInterface $resultValueRepository
     * @param ResultValueFactory $resultValueFactory
     */
    public function __construct(
        ResultValueRepositoryInterface $resultValueRepository,
        ResultValueFactory             $resultValueFactory
    )
    {
        $this->resultValueFactory    = $resultValueFactory;
        $this->resultValueRepository = $resultValueRepository;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws CouldNotSaveException
     */
    public function convert(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $select     = $connection->select()->from($setup->getTable(self::TABLE_RESULT_VALUE));
        $query      = $select->query()->fetchAll();
        foreach ($query as $oldData) {
            $connection->insertOnDuplicate($setup->getTable(ResultValueTable::TABLE_NAME), [
                ResultValueInterface::ID => $oldData[self::ID],
                ResultValueInterface::RESULT_ID => $oldData[self::RESULT_ID],
                ResultValueInterface::FIELD_ID => $oldData[self::FIELD_ID]
            ]);
            $resultValue = $this->resultValueFactory->create();
            $resultValue->setData($this->convertV2Data($oldData));
            $this->resultValueRepository->save($resultValue);
        }
    }

    /**
     * @param array $oldData
     * @return array
     */
    public function convertV2Data(array $oldData): array
    {
        return [
            ResultValueInterface::ID => $oldData[self::ID] ?? null,
            ResultValueInterface::RESULT_ID => $oldData[self::RESULT_ID] ?? null,
            ResultValueInterface::FIELD_ID => $oldData[self::FIELD_ID] ?? null,
            ResultValueInterface::VALUE => $oldData[self::VALUE] ?? null
        ];
    }
}
