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


use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Model\ResultFactory;
use MageMe\WebForms\Setup\Table\ResultTable;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class ResultConverter
{
    /**#@+
     * V2 constants
     */
    const ID = 'id';
    const WEBFORM_ID = 'webform_id';
    const STORE_ID = 'store_id';
    const CUSTOMER_ID = 'customer_id';
    const CUSTOMER_IP = 'customer_ip';
    const APPROVED = 'approved';
    const CREATED_TIME = 'created_time';
    const UPDATE_TIME = 'update_time';
    const PAGE_INFO = 'page_info';
    /**#@-*/

    const TABLE_RESULT = 'webforms_results';

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var ResultRepositoryInterface
     */
    private $resultRepository;

    /**
     * ResultConverter constructor.
     * @param ResultRepositoryInterface $resultRepository
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        ResultRepositoryInterface $resultRepository,
        ResultFactory             $resultFactory
    )
    {
        $this->resultFactory    = $resultFactory;
        $this->resultRepository = $resultRepository;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws CouldNotSaveException
     */
    public function convert(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $select     = $connection->select()->from($setup->getTable(self::TABLE_RESULT));
        $query      = $select->query()->fetchAll();
        foreach ($query as $oldData) {
            $connection->insertOnDuplicate($setup->getTable(ResultTable::TABLE_NAME), [
                ResultInterface::ID => $oldData[self::ID],
                ResultInterface::FORM_ID => $oldData[self::WEBFORM_ID]
            ]);
            $result = $this->resultFactory->create();
            $result->setData($this->convertV2Data($oldData));
            $this->resultRepository->save($result);
        }
    }

    /**
     * @param array $oldData
     * @return array
     */
    public function convertV2Data(array $oldData): array
    {
        return [
            ResultInterface::ID => $oldData[self::ID] ?? null,
            ResultInterface::FORM_ID => $oldData[self::WEBFORM_ID] ?? null,
            ResultInterface::STORE_ID => $oldData[self::STORE_ID] ?? null,
            ResultInterface::CUSTOMER_ID => $oldData[self::CUSTOMER_ID] ?? null,
            ResultInterface::CUSTOMER_IP => isset($oldData[self::CUSTOMER_IP]) ? is_long($oldData[self::CUSTOMER_IP]) ? long2ip($oldData[self::CUSTOMER_IP]) : null : null,
            ResultInterface::SUBMITTED_FROM_SERIALIZED => $oldData[self::PAGE_INFO] ?? null,
            ResultInterface::APPROVED => $oldData[self::APPROVED] ?? null,
            ResultInterface::CREATED_AT => $oldData[self::CREATED_TIME] ?? null,
            ResultInterface::UPDATED_AT => $oldData[self::UPDATE_TIME] ?? null,
            ResultInterface::IS_READ => 1,
        ];
    }
}
