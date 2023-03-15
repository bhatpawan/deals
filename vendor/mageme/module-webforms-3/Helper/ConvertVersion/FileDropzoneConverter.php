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


use MageMe\WebForms\Api\Data\FileDropzoneInterface;
use MageMe\WebForms\Api\FileDropzoneRepositoryInterface;
use MageMe\WebForms\Model\FileDropzoneFactory;
use MageMe\WebForms\Setup\Table\FileDropzoneTable;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class FileDropzoneConverter
{
    /**#@+
     * V2 constants
     */
    const ID = 'id';
    const RESULT_ID = 'result_id';
    const FIELD_ID = 'field_id';
    const NAME = 'name';
    const SIZE = 'size';
    const MIME_TYPE = 'mime_type';
    const PATH = 'path';
    const LINK_HASH = 'link_hash';
    const CREATED_TIME = 'created_time';
    /**#@-*/

    const TABLE_FILE_DROPZONE = 'webforms_files';

    /**
     * @var FileDropzoneFactory
     */
    private $fileDropzoneFactory;

    /**
     * @var FileDropzoneRepositoryInterface
     */
    private $fileDropzoneRepository;

    /**
     * FileDropzoneConverter constructor.
     * @param FileDropzoneRepositoryInterface $fileDropzoneRepository
     * @param FileDropzoneFactory $fileDropzoneFactory
     */
    public function __construct(
        FileDropzoneRepositoryInterface $fileDropzoneRepository,
        FileDropzoneFactory             $fileDropzoneFactory
    )
    {
        $this->fileDropzoneFactory    = $fileDropzoneFactory;
        $this->fileDropzoneRepository = $fileDropzoneRepository;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws CouldNotSaveException
     */
    public function convert(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        if (!$connection->isTableExists($setup->getTable(self::TABLE_FILE_DROPZONE))) {
            return;
        }
        $select = $connection->select()->from($setup->getTable(self::TABLE_FILE_DROPZONE));
        $query  = $select->query()->fetchAll();
        foreach ($query as $oldData) {
            $connection->insertOnDuplicate($setup->getTable(FileDropzoneTable::TABLE_NAME), [
                FileDropzoneInterface::ID => $oldData[self::ID],
                FileDropzoneInterface::RESULT_ID => $oldData[self::RESULT_ID],
                FileDropzoneInterface::FIELD_ID => $oldData[self::FIELD_ID],
                FileDropzoneInterface::NAME => $oldData[self::NAME],
                FileDropzoneInterface::SIZE => $oldData[self::SIZE],
                FileDropzoneInterface::MIME_TYPE => $oldData[self::MIME_TYPE],
                FileDropzoneInterface::PATH => $oldData[self::PATH],
                FileDropzoneInterface::LINK_HASH => $oldData[self::LINK_HASH]
            ]);
            $file = $this->fileDropzoneFactory->create();
            $file->setData($this->convertV2Data($oldData));
            $this->fileDropzoneRepository->save($file);
        }
    }

    /**
     * @param array $oldData
     * @return array
     */
    public function convertV2Data(array $oldData): array
    {
        return [
            FileDropzoneInterface::ID => $oldData[self::ID] ?? null,
            FileDropzoneInterface::RESULT_ID => $oldData[self::RESULT_ID] ?? null,
            FileDropzoneInterface::FIELD_ID => $oldData[self::FIELD_ID] ?? null,
            FileDropzoneInterface::NAME => $oldData[self::NAME] ?? null,
            FileDropzoneInterface::SIZE => $oldData[self::SIZE] ?? null,
            FileDropzoneInterface::MIME_TYPE => $oldData[self::MIME_TYPE] ?? null,
            FileDropzoneInterface::PATH => $oldData[self::PATH] ?? null,
            FileDropzoneInterface::LINK_HASH => $oldData[self::LINK_HASH] ?? null,
            FileDropzoneInterface::CREATED_AT => $oldData[self::CREATED_TIME] ?? null
        ];
    }
}
