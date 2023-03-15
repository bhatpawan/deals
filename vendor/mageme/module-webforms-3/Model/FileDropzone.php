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

namespace MageMe\WebForms\Model;


use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Model\File\AbstractFileDropzone;
use MageMe\WebForms\Model\File\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Backend\Model\Url as BackendUrl;
use Magento\Framework\Url as FrontendUrl;

class FileDropzone extends AbstractFileDropzone
{
    /**
     * @var  ResultInterface|null
     */
    protected $result = null;
    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;
    /**
     * @var BackendUrl
     */
    protected $backendUrl;
    /**
     * @var FrontendUrl
     */
    protected $frontendUrl;

    /**
     * FileDropzone constructor.
     *
     * @param FrontendUrl $frontendUrl
     * @param BackendUrl $backendUrl
     * @param ResultRepositoryInterface $resultRepository
     * @param Context $context
     */
    public function __construct(
        FrontendUrl $frontendUrl,
        BackendUrl $backendUrl,
        ResultRepositoryInterface $resultRepository,
        File\Context              $context
    )
    {
        parent::__construct($context);
        $this->resultRepository = $resultRepository;
        $this->backendUrl = $backendUrl;
        $this->frontendUrl = $frontendUrl;
    }

    /**
     * @return bool|FormInterface
     */
    public function getWebform()
    {
        $result = $this->getResult();
        if ($result) {
            return $result->getForm();
        }
        return false;
    }

    /**
     * @return bool|ResultInterface
     */
    public function getResult()
    {
        if ($this->result) {
            return $this->result;
        }
        if ($this->getResultId()) {
            try {
                $this->result = $this->resultRepository->getById($this->getResultId());
            } catch (NoSuchEntityException $e) {
                return false;
            }
            return $this->result;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getStore(?int $storeId = null)
    {
        $result = $this->getResult();
        if ($result) {
            $storeId = $result->getStoreId();
        }
        return parent::getStore($storeId);
    }

    /**
     * Return html for download file
     *
     * @return string
     * @throws NoSuchEntityException
     * @throws NoSuchEntityException
     */
    public function getDownloadHtml(): string
    {
        $value = '';
        if (file_exists($this->getFullPath())) {
            $value .= '<div>';
            $value .= '<a class="grid-button-action webforms-result-file-link" href="' . $this->getDownloadLink() . '">' . $this->getName() . ' <span>[' . $this->getSizeText() . ']</span></a>';
            $value .= '</div>';
        }
        return $value;
    }

    /**
     * Get download link
     *
     * @param bool $adminhtml
     * @return bool|string
     */
    public function getDownloadLink(bool $adminhtml = true)
    {
        if ($adminhtml) {
            return $this->backendUrl->getUrl('webforms/file/dropzoneDownload', ['hash' => $this->getLinkHash()]);
        }
        if ($this->frontendUrl) {
            return $this->frontendUrl->getUrl('webforms/file/dropzoneDownload', ['hash' => $this->getLinkHash()]);
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\FileDropzone::class);
    }
}
