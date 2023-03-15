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

namespace MageMe\WebForms\Controller\File;

use MageMe\WebForms\Api\FileDropzoneRepositoryInterface;
use MageMe\WebForms\Controller\AbstractAction;
use MageMe\WebForms\Model\FileDropzone;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Result\PageFactory;

class DropzoneDownload extends AbstractAction
{
    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var SessionManagerInterface
     */
    protected $session;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var FileDropzoneRepositoryInterface
     */
    protected $fileDropzoneRepository;

    /**
     * DropzoneDownload constructor.
     * @param Context $context
     * @param FileDropzoneRepositoryInterface $fileDropzoneRepository
     * @param Filesystem $filesystem
     * @param SessionManagerInterface $session
     * @param ResultFactory $resultFactory
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context                         $context,
        FileDropzoneRepositoryInterface $fileDropzoneRepository,
        Filesystem                      $filesystem,
        SessionManagerInterface         $session,
        ResultFactory                   $resultFactory,
        PageFactory                     $pageFactory
    )
    {
        parent::__construct($context, $pageFactory);
        $this->resultFactory          = $resultFactory;
        $this->session                = $session;
        $this->filesystem             = $filesystem;
        $this->fileDropzoneRepository = $fileDropzoneRepository;
    }

    /**
     * @inheritDoc
     * @throws FileSystemException
     * @throws NoSuchEntityException
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function execute()
    {
        $hash = $this->request->getParam('hash');

        if ($hash) {

            /** @var FileDropzone $file */
            $file   = $this->fileDropzoneRepository->getByHash($hash);
            $result = $file->getResult();
            if ($result) {
                $form = $result->getForm();
                if ($form && $form->getIsFrontendDownloadAllowed()) {
                    if (file_exists($file->getFullPath())) {
                        $fileName    = $file->getName();
                        $contentType = $file->getMimeType();

                        $this->response->setHttpResponseCode(
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
                            $this->response->setHeader('Content-Length', $fileSize);
                        }

                        $this->response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);

                        $this->response->clearBody();
                        $this->response->sendHeaders();

                        $handle = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->openFile($file->getPath());
                        $file   = "";
                        while (true == ($buffer = $handle->read(1024))) {
                            $file .= $buffer;
                        }
                        return $this->response->setBody($file);
                    }
                }
            }
        }

        /** @var Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $resultForward->forward('noroute');
        return $resultForward;
    }

}
