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

namespace MageMe\WebForms\Model\ResourceModel;

use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\FileMessageRepositoryInterface;
use MageMe\WebForms\Setup\Table\MessageTable;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Message resource model
 *
 */
class Message extends AbstractDb
{
    const DB_TABLE = MessageTable::TABLE_NAME;
    const ID_FIELD = MessageInterface::ID;

    /**
     * @inheritdoc
     */
    protected $nullableFK = [
        MessageInterface::USER_ID
    ];

    /**
     * @var FileMessageRepositoryInterface
     */
    protected $fileMessageRepository;

    /**
     * Message constructor.
     * @param FileMessageRepositoryInterface $fileMessageRepository
     * @param Context $context
     * @param string|null $connectionName
     */
    public function __construct(
        FileMessageRepositoryInterface $fileMessageRepository,
        Context                        $context,
        ?string                        $connectionName = null)
    {
        parent::__construct($context, $connectionName);
        $this->fileMessageRepository = $fileMessageRepository;
    }

    /**
     * @param AbstractModel|MessageInterface $object
     * @return Message
     * @throws CouldNotDeleteException
     */
    protected function _beforeDelete(AbstractModel $object): Message
    {
        //delete files
        $files = $this->fileMessageRepository->getListByMessageId($object->getId())->getItems();
        foreach ($files as $file) {
            $this->fileMessageRepository->delete($file);
        }
        return parent::_beforeDelete($object);
    }
}
