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


use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\MessageRepositoryInterface;
use MageMe\WebForms\Model\MessageFactory;
use MageMe\WebForms\Setup\Table\MessageTable;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class MessageConverter
{
    /**#@+
     *  V2 constants
     */
    const ID = 'id';
    const RESULT_ID = 'result_id';
    const USER_ID = 'user_id';
    const MESSAGE = 'message';
    const AUTHOR = 'author';
    const IS_CUSTOMER_EMAILED = 'is_customer_emailed';
    const CREATED_TIME = 'created_time';
    /**#@-*/

    const TABLE_MESSAGE = 'webforms_message';

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;

    /**
     * MessageConverter constructor.
     * @param MessageRepositoryInterface $messageRepository
     * @param MessageFactory $messageFactory
     */
    public function __construct(
        MessageRepositoryInterface $messageRepository,
        MessageFactory             $messageFactory
    )
    {
        $this->messageFactory    = $messageFactory;
        $this->messageRepository = $messageRepository;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws CouldNotSaveException
     */
    public function convert(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $select     = $connection->select()->from($setup->getTable(self::TABLE_MESSAGE));
        $query      = $select->query()->fetchAll();
        foreach ($query as $oldData) {
            $connection->insertOnDuplicate($setup->getTable(MessageTable::TABLE_NAME), [
                MessageInterface::ID => $oldData[self::ID],
                MessageInterface::RESULT_ID => $oldData[self::RESULT_ID]
            ]);
            $message = $this->messageFactory->create();
            $message->setData($this->convertV2Data($oldData));
            $this->messageRepository->save($message);
        }
    }

    /**
     * @param array $oldData
     * @return array
     */
    public function convertV2Data(array $oldData): array
    {
        return [
            MessageInterface::ID => $oldData[self::ID] ?? null,
            MessageInterface::RESULT_ID => $oldData[self::RESULT_ID] ?? null,
            MessageInterface::USER_ID => $oldData[self::USER_ID] ?? null,
            MessageInterface::MESSAGE => $oldData[self::MESSAGE] ?? null,
            MessageInterface::AUTHOR => $oldData[self::AUTHOR] ?? null,
            MessageInterface::IS_CUSTOMER_EMAILED => $oldData[self::IS_CUSTOMER_EMAILED] ?? null,
            MessageInterface::CREATED_AT => $oldData[self::CREATED_TIME] ?? null,
        ];
    }
}
