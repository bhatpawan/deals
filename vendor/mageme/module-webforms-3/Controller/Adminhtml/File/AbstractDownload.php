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

namespace MageMe\WebForms\Controller\Adminhtml\File;


use MageMe\WebForms\Api\RepositoryInterface;
use MageMe\WebForms\Model\File\AbstractFile;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\File\ReadInterface;

class AbstractDownload extends Action
{
    /**
     * Resource open handle
     *
     * @var ReadInterface
     */
    protected $_handle = null;

    /**
     * @var Filesystem\Directory\ReadInterface
     */
    protected $workingDirectory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * Download constructor.
     * @param RepositoryInterface $repository
     * @param Filesystem $filesystem
     * @param Context $context
     */
    public function __construct(
        RepositoryInterface $repository,
        Filesystem          $filesystem,
        Context             $context
    )
    {
        parent::__construct($context);
        $this->filesystem = $filesystem;
        $this->repository = $repository;
    }

    /**
     * @inheritdoc
     * @throws FileSystemException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $hash = $this->getRequest()->getParam('hash');
        if ($hash) {

            /** @var AbstractFile $file */
            $file = $this->repository->getByHash($hash);
            if (file_exists($file->getFullPath())) {
                $fileName    = $file->getName();
                $contentType = $file->getMimeType();

                $this->getResponse()->setHttpResponseCode(
                    200
                )->setHeader(
                    'Pragma',
                    'public',
                    true
                )->setHeader(
                    'Cache-Control',
                    'must-revalidate, post-check=0, pre-check=0',
                    true
                )->setHeader(
                    'Content-type',
                    $contentType,
                    true
                );
                if ($fileSize = $file->getSize()) {
                    $this->getResponse()->setHeader('Content-Length', $fileSize);
                }

                $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
                $this->getResponse()->clearBody();
                $this->getResponse()->sendHeaders();

                $this->workingDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $handle                 = $this->workingDirectory->openFile($file->getPath());

                $file = "";
                while (true == ($buffer = $handle->read(1024))) {
                    $file .= $buffer;
                }
                return $this->getResponse()->setBody($file);
            }
        }

        /** @var Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $resultForward->forward('noroute');
        return $resultForward;
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('MageMe_WebForms::manage_forms');
    }
}