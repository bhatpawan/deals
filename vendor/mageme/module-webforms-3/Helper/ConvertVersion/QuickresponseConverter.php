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


use MageMe\WebForms\Api\Data\QuickresponseInterface;
use MageMe\WebForms\Api\QuickresponseRepositoryInterface;
use MageMe\WebForms\Model\QuickresponseFactory;
use MageMe\WebForms\Setup\Table\QuickresponseTable;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class QuickresponseConverter
{
    /**#@+
     * V2 constants
     */
    const ID = 'id';
    const TITLE = 'title';
    const MESSAGE = 'message';
    const CREATED_TIME = 'created_time';
    const UPDATE_TIME = 'update_time';
    /**#@-*/

    const TABLE_QUICKRESPONSE = 'webforms_quickresponse';

    /**
     * @var QuickresponseFactory
     */
    private $quickresponseFactory;

    /**
     * @var QuickresponseRepositoryInterface
     */
    private $quickresponseRepository;

    /**
     * QuickresponseConverter constructor.
     * @param QuickresponseRepositoryInterface $quickresponseRepository
     * @param QuickresponseFactory $quickresponseFactory
     */
    public function __construct(
        QuickresponseRepositoryInterface $quickresponseRepository,
        QuickresponseFactory             $quickresponseFactory
    )
    {
        $this->quickresponseFactory    = $quickresponseFactory;
        $this->quickresponseRepository = $quickresponseRepository;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws CouldNotSaveException
     */
    public function convert(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $select     = $connection->select()->from($setup->getTable(self::TABLE_QUICKRESPONSE));
        $query      = $select->query()->fetchAll();
        foreach ($query as $oldData) {
            $connection->insertOnDuplicate($setup->getTable(QuickresponseTable::TABLE_NAME), [
                QuickresponseInterface::ID => $oldData[self::ID]
            ]);
            $quickresponse = $this->quickresponseFactory->create();
            $quickresponse->setData($this->convertV2Data($oldData));
            $this->quickresponseRepository->save($quickresponse);
        }
    }

    /**
     * @param array $oldData
     * @return array
     */
    public function convertV2Data(array $oldData): array
    {
        return [
            QuickresponseInterface::ID => $oldData[self::ID] ?? null,
            QuickresponseInterface::TITLE => $oldData[self::TITLE] ?? null,
            QuickresponseInterface::MESSAGE => $oldData[self::MESSAGE] ?? null,
            QuickresponseInterface::CREATED_AT => $oldData[self::CREATED_TIME] ?? null,
            QuickresponseInterface::UPDATED_AT => $oldData[self::UPDATE_TIME] ?? null
        ];
    }
}
