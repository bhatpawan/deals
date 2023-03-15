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

namespace MageMe\WebForms\Setup;

use MageMe\WebForms\Setup\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var Table\FormTable
     */
    protected $formTable;

    /**
     * @var Table\FieldsetTable
     */
    protected $fieldsetTable;

    /**
     * @var Table\FieldTable
     */

    protected $fieldTable;

    /**
     * @var Table\LogicTable
     */
    protected $logicTable;

    /**
     * @var Table\ResultTable
     */
    protected $resultTable;

    /**
     * @var Table\ResultValueTable
     */
    protected $resultValueTable;

    /**
     * @var Table\MessageTable
     */
    protected $messageTable;

    /**
     * @var Table\StoreTable
     */
    protected $storeTable;

    /**
     * @var Table\QuickresponseCategoryTable
     */
    protected $quickresponseCategoryTable;

    /**
     * @var Table\QuickresponseTable
     */
    protected $quickresponseTable;

    /**
     * @var Table\TmpFileDropzoneTable
     */
    protected $tmpFileDropzoneTable;

    /**
     * @var Table\TmpFileMessageTable
     */
    protected $tmpFileMessageTable;

    /**
     * @var Table\TmpFileGalleryTable
     */
    protected $tmpFileGalleryTable;

    /**
     * @var Table\TmpFileCustomerNotificationTable
     */
    protected $tmpFileCustomerNotificationTable;

    /**
     * @var Table\FileDropzoneTable
     */
    protected $fileDropzoneTable;

    /**
     * @var Table\FileMessageTable
     */
    protected $fileMessageTable;

    /**
     * @var Table\FileGalleryTable
     */
    protected $fileGalleryTable;

    /**
     * @var Table\FileCustomerNotificationTable
     */
    protected $fileCustomerNotificationTable;

    /**
     * InstallSchema constructor.
     * @param Table\FormTable $formTable
     * @param Table\FieldsetTable $fieldsetTable
     * @param Table\FieldTable $fieldTable
     * @param Table\LogicTable $logicTable
     * @param Table\ResultTable $resultTable
     * @param Table\ResultValueTable $resultValueTable
     * @param Table\MessageTable $messageTable
     * @param Table\StoreTable $storeTable
     * @param Table\QuickresponseCategoryTable $quickresponseCategoryTable
     * @param Table\QuickresponseTable $quickresponseTable
     * @param Table\TmpFileDropzoneTable $tmpFileDropzoneTable
     * @param Table\TmpFileMessageTable $tmpFileMessageTable
     * @param Table\TmpFileGalleryTable $tmpFileGalleryTable
     * @param Table\TmpFileCustomerNotificationTable $tmpFileCustomerNotificationTable
     * @param Table\FileDropzoneTable $fileDropzoneTable
     * @param Table\FileMessageTable $fileMessageTable
     * @param Table\FileGalleryTable $fileGalleryTable
     * @param Table\FileCustomerNotificationTable $fileCustomerNotificationTable
     */
    public function __construct(
        Table\FormTable                        $formTable,
        Table\FieldsetTable                    $fieldsetTable,
        Table\FieldTable                       $fieldTable,
        Table\LogicTable                       $logicTable,
        Table\ResultTable                      $resultTable,
        Table\ResultValueTable                 $resultValueTable,
        Table\MessageTable                     $messageTable,
        Table\StoreTable                       $storeTable,
        Table\QuickresponseCategoryTable       $quickresponseCategoryTable,
        Table\QuickresponseTable               $quickresponseTable,
        Table\TmpFileDropzoneTable             $tmpFileDropzoneTable,
        Table\TmpFileMessageTable              $tmpFileMessageTable,
        Table\TmpFileGalleryTable              $tmpFileGalleryTable,
        Table\TmpFileCustomerNotificationTable $tmpFileCustomerNotificationTable,
        Table\FileDropzoneTable                $fileDropzoneTable,
        Table\FileMessageTable                 $fileMessageTable,
        Table\FileGalleryTable                 $fileGalleryTable,
        Table\FileCustomerNotificationTable    $fileCustomerNotificationTable
    )
    {
        $this->formTable                        = $formTable;
        $this->fieldsetTable                    = $fieldsetTable;
        $this->fieldTable                       = $fieldTable;
        $this->logicTable                       = $logicTable;
        $this->resultTable                      = $resultTable;
        $this->resultValueTable                 = $resultValueTable;
        $this->messageTable                     = $messageTable;
        $this->storeTable                       = $storeTable;
        $this->quickresponseCategoryTable       = $quickresponseCategoryTable;
        $this->quickresponseTable               = $quickresponseTable;
        $this->tmpFileDropzoneTable             = $tmpFileDropzoneTable;
        $this->tmpFileMessageTable              = $tmpFileMessageTable;
        $this->tmpFileGalleryTable              = $tmpFileGalleryTable;
        $this->tmpFileCustomerNotificationTable = $tmpFileCustomerNotificationTable;
        $this->fileDropzoneTable                = $fileDropzoneTable;
        $this->fileMessageTable                 = $fileMessageTable;
        $this->fileGalleryTable                 = $fileGalleryTable;
        $this->fileCustomerNotificationTable    = $fileCustomerNotificationTable;
    }

    /**
     * @inheritDoc
     * @throws Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->formTable->createTable($setup);
        $this->fieldsetTable->createTable($setup);
        $this->fieldTable->createTable($setup);
        $this->logicTable->createTable($setup);
        $this->resultTable->createTable($setup);
        $this->resultValueTable->createTable($setup);
        $this->messageTable->createTable($setup);
        $this->storeTable->createTable($setup);
        $this->quickresponseCategoryTable->createTable($setup);
        $this->quickresponseTable->createTable($setup);
        $this->tmpFileDropzoneTable->createTable($setup);
        $this->tmpFileMessageTable->createTable($setup);
        $this->tmpFileGalleryTable->createTable($setup);
        $this->tmpFileCustomerNotificationTable->createTable($setup);
        $this->fileDropzoneTable->createTable($setup);
        $this->fileMessageTable->createTable($setup);
        $this->fileGalleryTable->createTable($setup);
        $this->fileCustomerNotificationTable->createTable($setup);
        $setup->endSetup();
    }
}
