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

namespace MageMe\WebForms\Block\Form\Element\Field\Type;


use MageMe\WebForms\Api\Data\FileDropzoneInterface;
use MageMe\WebForms\Block\Form\Element\Field\Tooltip;
use MageMe\WebForms\Helper\FileHelper;
use MageMe\WebForms\Helper\TranslationHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

class Image extends File
{
    /**
     * @var int
     */
    protected $thumbnailWidth = 100;
    /**
     * @var int
     */
    protected $thumbnailHeight = 100;

    public function __construct(
        TranslationHelper    $translationHelper,
        Tooltip              $tooltipBlock,
        ScopeConfigInterface $scopeConfig,
        FileHelper           $fileHelper,
        Registry             $registry,
        Context              $context,
        array                $data = [])
    {
        parent::__construct($translationHelper, $tooltipBlock, $fileHelper, $registry, $context, $data);
        $this->thumbnailWidth  = $scopeConfig->getValue('webforms/images/grid_thumbnail_width');
        $this->thumbnailHeight = $scopeConfig->getValue('webforms/images/grid_thumbnail_height');
    }

    /**
     * @inheritdoc
     */
    public function getFilePreviewHtml(FileDropzoneInterface $file): string
    {
        $thumbnail = $file->getThumbnail(100);
        if (!$thumbnail) {
            return parent::getFilePreviewHtml($file);
        }
        return '<a class="webforms-image-box webforms-file-link" href="' . $file->getDownloadLink(false) . '">
                    <figure>
                        <p><img src="' . $file->getThumbnail($this->thumbnailWidth, $this->thumbnailHeight) . '"/></p>
                        <figcaption>' . $file->getName() . ' <span>[' . $file->getSizeText() . ']</span></figcaption>
                    </figure>
                </a>';
    }

}
