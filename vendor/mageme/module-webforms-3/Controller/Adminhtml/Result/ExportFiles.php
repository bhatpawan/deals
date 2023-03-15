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

namespace MageMe\WebForms\Controller\Adminhtml\Result;


use Exception;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use ZipArchive;

class ExportFiles extends Index
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * ExportFiles constructor.
     * @param ResultRepositoryInterface $resultRepository
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     * @param FormRepositoryInterface $formRepository
     * @param AccessHelper $accessHelper
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        ResultRepositoryInterface $resultRepository,
        FileFactory               $fileFactory,
        Filesystem                $filesystem,
        FormRepositoryInterface   $formRepository,
        AccessHelper              $accessHelper,
        PageFactory               $resultPageFactory,
        Registry                  $registry,
        Context                   $context
    )
    {
        parent::__construct($formRepository, $accessHelper, $resultPageFactory, $registry, $context);
        $this->fileFactory      = $fileFactory;
        $this->filesystem       = $filesystem;
        $this->resultRepository = $resultRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if (!class_exists('\ZipArchive')) {
            die('ZipArchive class not found');
        }
        $tmpPath = $this->filesystem->getDirectoryRead(DirectoryList::TMP)->getAbsolutePath();
        $id      = (int)$this->getRequest()->getParam(ResultInterface::ID);

        if (!$id) {
            return $this->getResponse()->setRedirect($this->_redirect->getRefererUrl());
        }
        try {
            $result = $this->resultRepository->getById($id);
            $files  = $result->getFiles();

            if (!count($files)) {
                throw new NotFoundException(__('Files not found.'));
            }

            $archiveName = 'Files_' . $id . '.zip';
            $archivePath = $tmpPath . '/' . $archiveName;

            $zip = new ZipArchive();
            $zip->open($archivePath, ZipArchive::CREATE);
            $fileNames = [];

            foreach ($files as $file) {
                $filePath = $file->getFullPath();
                $fileName = $file->getName();

                // Add index to files with same names
                if (in_array($fileName, $fileNames)) {
                    $arr        = array_count_values($fileNames);
                    $path_parts = pathinfo((string)$fileName);
                    $fileName   = $path_parts['filename'] . '-' . $arr[$fileName];
                    if ($path_parts['extension']) {
                        $fileName .= '.' . $path_parts['extension'];
                    }
                }

                if (file_exists($file->getFullPath())) {
                    $zip->addFile($filePath, $fileName);
                    $fileNames[] = $file->getName();
                }
            }
            $zip->close();

            return $this->fileFactory->create(
                $archiveName,
                [
                    'type' => 'filename',
                    'value' => $archivePath,
                    'rm' => true
                ],
                DirectoryList::VAR_DIR,
                'application/zip'
            );

        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->getResponse()->setRedirect($this->_redirect->getRefererUrl());
    }
}