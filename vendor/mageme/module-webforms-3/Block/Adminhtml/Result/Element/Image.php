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

namespace MageMe\WebForms\Block\Adminhtml\Result\Element;

use MageMe\WebForms\Model\FileDropzone;
use Magento\Framework\Exception\LocalizedException;

/**
 *
 */
class Image extends File
{
    const TYPE = 'image';

    /**
     * @return string
     * @throws LocalizedException
     */
    protected function _getPreviewHtml(): string
    {
        $html = '';
        if ($this->getData('result_id')) {
            $result   = $this->resultFactory->create()->load($this->getData('result_id'));
            $field_id = $this->getData('field_id');
            $files    = $this->fileDropzoneRepository->getListByResultAndFieldId(
                $result->getId(),
                $field_id
            )->getItems();
            $width    = $this->scopeConfig->getValue('webforms/images/grid_thumbnail_width');
            $height   = $this->scopeConfig->getValue('webforms/images/grid_thumbnail_height');

            if (count($files)) {
                $html .= '<div id="' . $this->getUid() . 'filepool" class="webforms-file-pool">';
                if (count($files) > 1) {

                    $html .= $this->_getSelectAllHtml();
                }
                /** @var FileDropzone $file */
                foreach ($files as $file) {
                    $html .= '<div class="webforms-file-cell">';

                    if (file_exists($file->getFullPath())) {
                        $fileName = $this->fileHelper->getShortFilename($file->getName());

                        $thumbnail = $file->getThumbnail(100);
                        if ($thumbnail) {
                            $html .= '<a class="webforms-image-box webforms-file-link" href="' . $file->getDownloadLink(true) . '">
                            <figure>
                                <p><img src="' . $file->getThumbnail($width, $height) . '"/></p>
                                <figcaption>' . $file->getName() . ' <span>[' . $file->getSizeText() . ']</span></figcaption>
                            </figure>
                        </a>';
                        } else {
                            $html .= '<nobr><a class="webforms-file-link" href="' . $file->getDownloadLink(true) . '">' . $fileName . ' <small>[' . $file->getSizeText() . ']</small></a></nobr>';
                        }
                    }
                    $html .= $this->_getDeleteCheckboxHtml($file);

                    $html .= '</div>';

                }
                $html .= '</div>';
            }

        }
        return $html;

    }

}
