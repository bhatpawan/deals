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


use MageMe\WebForms\Model\File\AbstractFileMessage;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;

class FileMessage extends AbstractFileMessage
{
    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * FileMessage constructor.
     * @param UrlInterface $url
     * @param File\Context $context
     */
    public function __construct(
        UrlInterface $url,
        File\Context $context
    )
    {
        parent::__construct($context);
        $this->url = $url;
    }

    /**
     * Return html for download file
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getDownloadHtml(): string
    {
        $value = '';
        if (file_exists($this->getFullPath())) {
            $value .= '<div>';
            $value .= '<a class="webforms-file-link" href="' . $this->getDownloadLink() . '">' . $this->getName() . ' <span>[' . $this->getSizeText() . ']</span></a>';
            $value .= '</div>';
        }
        return $value;
    }

    /**
     * Get download link
     *
     * @return string
     */
    public function getDownloadLink(): string
    {
        return $this->url->getUrl('webforms/file/messageDownload', ['hash' => $this->getLinkHash()]);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\FileMessage::class);
    }

}
