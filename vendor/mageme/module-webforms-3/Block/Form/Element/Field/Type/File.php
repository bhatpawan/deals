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
use MageMe\WebForms\Block\Form\Element\Field\AbstractField;
use MageMe\WebForms\Block\Form\Element\Field\Tooltip;
use MageMe\WebForms\Helper\FileHelper;
use MageMe\WebForms\Helper\TranslationHelper;
use MageMe\WebForms\Model\FileDropzone;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

/**
 *
 */
class File extends AbstractField
{
    /**
     * Block's template
     * @var string
     */
    protected $_template = self::TEMPLATE_PATH . 'file.phtml';

    /**
     * @var FileHelper
     */
    protected $fileHelper;

    /**
     * File constructor.
     *
     * @param TranslationHelper $translationHelper
     * @param Tooltip $tooltipBlock
     * @param FileHelper $fileHelper
     * @param Registry $registry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        TranslationHelper $translationHelper,
        Tooltip           $tooltipBlock,
        FileHelper        $fileHelper,
        Registry          $registry,
        Context           $context,
        array             $data = [])
    {
        parent::__construct($translationHelper, $tooltipBlock, $registry, $context, $data);
        $this->fileHelper = $fileHelper;
    }

    /**
     * @return string
     */
    public function getFieldName(): string
    {
        return 'file_' . $this->field->getId();
    }

    /**
     * @return string
     */
    public function getFieldClass(): string
    {
        return 'input-file ' . parent::getFieldClass();
    }

    /**
     * @return string
     */
    public function getFieldStyle(): string
    {
        $style = parent::getFieldStyle();
        if ($this->getIsDropzone()) {
            $style .= ' display:none';
        }
        return $style;
    }

    /**
     * @return bool
     */
    public function getIsDropzone(): bool
    {
        return $this->field->getIsDropzone();
    }

    /**
     * @return string
     */
    public function getFormKey(): string
    {
        return $this->field->getFormKey();
    }

    /**
     * @return string
     */
    public function getDropzoneName(): string
    {
        return parent::getFieldName();
    }

    /**
     * @return string
     */
    public function getDropzoneText(): string
    {
        return $this->applyTranslation($this->field->getDropzoneText());
    }

    /**
     * @return int
     */
    public function getDropzoneMaxFiles(): int
    {
        return $this->field->getDropzoneMaxFiles();
    }

    /**
     * @return int
     */
    public function getUploadLimit(): int
    {
        return $this->field->getUploadLimit();
    }

    /**
     * @return array
     */
    public function getAllowedExtensions(): array
    {
        return $this->field->getAllowedExtensions();
    }

    /**
     * @return array
     */
    public function getRestrictedExtensions(): array
    {
        return $this->field->getRestrictedExtensions();
    }

    /**
     * @param FileDropzoneInterface|FileDropzone $file
     * @return string
     */
    public function getFilePreviewHtml(FileDropzoneInterface $file): string
    {
        return '<nobr>
                    <a class="webforms-file-link" href="' . $file->getDownloadLink(false) . '">
                        ' . $this->getFileHelper()->getShortFilename($file->getName()) . '<span>[' . $file->getSizeText() . ']</span>
                    </a>
                </nobr>';
    }

    /**
     * @return FileHelper
     */
    public function getFileHelper(): FileHelper
    {
        return $this->fileHelper;
    }

    /**
     * @return string
     */
    public function getSelectAllId(): string
    {
        return $this->getFieldUid() . 'selectall';
    }

    /**
     * @return Phrase
     */
    public function getSelectAllLabel(): Phrase
    {
        return __('Select All');
    }

    /**
     * @param $id
     * @return string
     */
    public function getCheckboxId($id): string
    {
        return 'delete_file_' . $id;
    }

    /**
     * @return string
     */
    public function getCheckboxName(): string
    {
        return 'delete_file_' . $this->field->getId() . '[]';
    }

    /**
     * @return Phrase
     */
    public function getCheckboxLabel(): Phrase
    {
        return __('Delete');
    }
}
