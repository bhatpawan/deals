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

use MageMe\WebForms\Controller\AbstractAction;
use MageMe\WebForms\Model\Repository\TmpFileDropzoneRepository;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\HttpFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;

class DropzoneRemove extends AbstractAction
{
    /**
     * @var HttpFactory
     */
    private $httpFactory;
    /**
     * @var TmpFileDropzoneRepository
     */
    private $tmpFileDropzoneRepository;

    /**
     * @param TmpFileDropzoneRepository $tmpFileDropzoneRepository
     * @param HttpFactory $httpFactory
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        TmpFileDropzoneRepository $tmpFileDropzoneRepository,
        HttpFactory               $httpFactory,
        Context                   $context,
        PageFactory               $pageFactory
    ) {
        parent::__construct($context, $pageFactory);

        $this->httpFactory               = $httpFactory;
        $this->tmpFileDropzoneRepository = $tmpFileDropzoneRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $result = [
            'success' => false,
            'error' => ''
        ];
        $hash   = $this->request->getParam('hash');
        try {
            $file = $this->tmpFileDropzoneRepository->getByHash($hash);
            $this->tmpFileDropzoneRepository->delete($file);
            $result['success'] = true;
        } catch (NoSuchEntityException|CouldNotDeleteException $e) {
            $result['error'] = $e->getMessage();
        }
        $json       = json_encode($result);
        $resultHttp = $this->httpFactory->create();
        $resultHttp->setNoCacheHeaders();
        $resultHttp->setHeader('Content-Type', 'text/plain', true);
        $resultHttp->setHeader('X-Robots-Tag', 'noindex', true);
        return $resultHttp->setContent($json);
    }
}