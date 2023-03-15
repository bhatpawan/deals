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

use MageMe\WebForms\Api\FileDropzoneRepositoryInterface;
use MageMe\WebForms\Helper\FileHelper;
use MageMe\WebForms\Model;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;

/**
 *
 */
class File extends AbstractElement
{
    const TYPE = 'file';

    /**
     * @var Model\ResultFactory
     */
    protected $resultFactory;

    /**
     * @var FileDropzoneRepositoryInterface
     */
    protected $fileDropzoneRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var FileHelper
     */
    protected $fileHelper;

    /**
     * @param FileHelper $fileHelper
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param Model\ResultFactory $resultFactory
     * @param FileDropzoneRepositoryInterface $fileDropzoneRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        FileHelper                      $fileHelper,
        Factory                         $factoryElement,
        CollectionFactory               $factoryCollection,
        Escaper                         $escaper,
        Model\ResultFactory             $resultFactory,
        FileDropzoneRepositoryInterface $fileDropzoneRepository,
        ScopeConfigInterface            $scopeConfig,
        array $data = []
    )
    {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('file');
        $this->setExtType('file');
        if (isset($data['value'])) {
            $this->setValue($data['value']);
        }
        $this->fileDropzoneRepository = $fileDropzoneRepository;
        $this->resultFactory          = $resultFactory;
        $this->scopeConfig            = $scopeConfig;
        $this->fileHelper             = $fileHelper;
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getElementHtml(): string
    {
        $this->addClass('input-file');
        if ($this->getRequired()) {
            $this->removeClass('required-entry');
        }

        $element = sprintf('<input id="%s" name="%s" data-uid="%s" %s />%s',
            $this->getHtmlId(),
            $this->_getName(),
            $this->getUid(),
            $this->serialize($this->getHtmlAttributes()),
            $this->getAfterElementHtml()
        );

        return $this->_getPreviewHtml() . $element . $this->_getDropzoneHtml();
    }

    /**
     * @param string $class
     * @return $this|File
     */
    public function removeClass($class): File
    {
        $classes = array_unique(explode(' ', (string)$this->getClass()));
        if (false !== ($key = array_search($class, $classes))) {
            unset($classes[$key]);
        }
        $this->setClass(implode(' ', $classes));
        return $this;
    }

    /**
     * @return string
     */
    public function _getName(): string
    {
        return "file_{$this->getData('field_id')}";
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
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
            if (count($files)) {
                $html .= '<div id="' . $this->getUid() . 'filepool" class="webforms-file-pool">';
                if (count($files) > 1) {
                    $html .= $this->_getSelectAllHtml();
                }

                /** @var Model\FileDropzone $file */
                foreach ($files as $file) {
                    $fileName = $this->fileHelper->getShortFilename($file->getName());

                    $html .= '<div class="webforms-file-cell">';

                    if (file_exists($file->getFullPath())) {
                        $html .= '<nobr><a class="webforms-file-link" href="' . $file->getDownloadLink(true) . '">' . $fileName . ' <span>[' . $file->getSizeText() . ']</span></a></nobr>';
                    }

                    $html .= $this->_getDeleteCheckboxHtml($file);

                    $html .= '</div>';

                }
                $html .= '</div>';
            }
        }

        return $html;
    }

    /**
     * @return string
     */
    protected function _getSelectAllHtml(): string
    {
        $id   = $this->getHtmlId() . 'selectall';
        $html = '<script>function checkAll(elem){elem.up().up().select("input[type=checkbox]").invoke("writeAttribute","checked",elem.checked);}</script>';
        $html .= '<div class="webforms-file-pool-selectall"><input id="' . $id . '" type="checkbox" class="webforms-file-delete-checkbox" onchange="checkAll(this)"/> <label for="' . $id . '">' . __('Select All') . '</label></div>';
        return $html;
    }

    /**
     * @param $file
     * @return string
     */
    protected function _getDeleteCheckboxHtml($file): string
    {
        $html = '';
        if ($file) {
            $checkboxId   = 'delete_file_' . $file->getId();
            $checkboxName = str_replace('file_', 'delete_file_', $this->getName()) . '[]';

            $checkbox = array(
                'type' => 'checkbox',
                'name' => $checkboxName,
                'value' => $file->getLinkHash(),
                'class' => 'webforms-file-delete-checkbox',
                'id' => $checkboxId,
            );

            $label = array(
                'for' => $checkboxId,
            );

            $html .= '<p>';
            $html .= $this->_drawElementHtml('input', $checkbox) . ' ';
            $html .= $this->_drawElementHtml('label', $label, false) . $this->_getDeleteCheckboxLabel() . '</label>';
            $html .= '</p>';
        }
        return $html;
    }

    /**
     * @param $element
     * @param array $attributes
     * @param bool $closed
     * @return string
     */
    protected function _drawElementHtml($element, array $attributes, bool $closed = true): string
    {
        $parts = array();
        foreach ($attributes as $k => $v) {
            $parts[] = sprintf('%s="%s"', $k, $v);
        }

        return sprintf('<%s %s%s>', $element, implode(' ', $parts), $closed ? ' /' : '');
    }

    /**
     * @return Phrase
     */
    protected function _getDeleteCheckboxLabel(): Phrase
    {
        return __('Delete');
    }

    /**
     * @return string
     */
    protected function _getDropzoneHtml(): string
    {
        $config = array();

        $config['uid']                          = $this->getUid();
        $config['url']                          = $this->getData('dropzone_url');
        $config['fieldId']                      = $this->getHtmlId();
        $config['fieldName']                    = $this->getDropzoneName();
        $config['dropZone']                     = $this->getData('dropzone') ? 1 : 0;
        $config['dropZoneText']                 = $this->getData('dropzone_text') ? $this->getData('dropzone_text') : __('Add files or drop here');
        $config['maxFiles']                     = $this->getData('dropzone_maxfiles') ? $this->getData('dropzone_maxfiles') : 5;
        $config['allowedSize']                  = $this->getData('allowed_size');
        $config['allowedExtensions']            = $this->getData('allowed_extensions');
        $config['restrictedExtensions']         = $this->getData('restricted_extensions');
        $config['validationCssClass']           = '';
        $config['errorMsgAllowedExtensions']    = __('Selected file has none of allowed extensions: %s');
        $config['errorMsgRestrictedExtensions'] = __('Uploading of potentially dangerous files is not allowed.');
        $config['errorMsgAllowedSize']          = __('Selected file exceeds allowed size: %s kB');
        $config['errorMsgUploading']            = __('Error uploading file');
        $config['errorMsgNotReady']             = __('Please wait... the upload is in progress.');

        return '<script>require([\'MageMe_WebForms/js/validation\'], function (initValidation) {initValidation();})</script>
                <script>require([\'MageMe_WebForms/js/dropzone\'], function (JsWebFormsDropzone) {new JsWebFormsDropzone(' . json_encode($config) . ')})</script>';

    }

    /**
     * @return array|mixed|string|null
     */
    public function getDropzoneName()
    {
        $name = $this->getData('dropzone_name');
        if ($suffix = $this->getForm()->getFieldNameSuffix()) {
            $name = $this->getForm()->addSuffixToName($name, $suffix);
        }
        return $name;
    }

    /**
     * @return string
     */
    protected function _getDeleteCheckboxSpanClass(): string
    {
        return 'delete-file';
    }

    /**
     * @return string
     */
    protected function getUid(): string
    {
        return str_replace('field', '', (string)$this->getId());
    }
}
