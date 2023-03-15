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

namespace MageMe\WebForms\Block\Adminhtml\Result;

use IntlDateFormatter;
use MageMe\WebForms\Api\Data\ResultInterface;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;

/**
 *
 */
class Edit extends Container
{
    /**
     * Core registry
     *
     * @var Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context  $context,
        Registry $registry,
        array    $data = []
    )
    {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Get edit form container header text
     *
     * @return Phrase
     */
    public function getHeaderText(): Phrase
    {
        /** @var ResultInterface $result */
        $result = $this->_coreRegistry->registry('webforms_result');
        if ($result->getId()) {
            return __("Result # %1 | %2", $result->getId(),
                $this->_localeDate->formatDate($result->getCreatedAt(),
                    IntlDateFormatter::MEDIUM, true));
        } else {
            return __('New Result');
        }
    }

    /**
     * @return string
     */
    public function getBackUrl(): string
    {
        if ($this->getRequest()->getParam(ResultInterface::CUSTOMER_ID)) {
            return $this->getUrl('customer/index/edit', ['id' => $this->getRequest()->getParam(ResultInterface::CUSTOMER_ID)]);
        }
        return $this->getUrl('*/*/', [ResultInterface::FORM_ID => $this->_coreRegistry->registry('webforms_form')->getId()]);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId   = 'result_id';
        $this->_blockGroup = 'MageMe_WebForms';
        $this->_controller = 'adminhtml_result';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Result'));
        $this->buttonList->update('delete', 'label', __('Delete Result'));

        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            -100
        );

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('block_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'block_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'block_content');
                }
            }
        ";

    }

    /**
     * @return string
     * @throws LocalizedException
     */
    protected function _toHtml(): string
    {
        $js = $this->getLayout()->createBlock('Magento\Framework\View\Element\Template', 'webforms_js', [
            'data' => [
                'template' => 'MageMe_WebForms::logic.phtml',
                'result' => $this->_coreRegistry->registry('webforms_result'),
                'form' => $this->_coreRegistry->registry('webforms_form'),
            ]
        ])->toHtml();
        return parent::_toHtml() . $js;
    }
}
