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

namespace MageMe\Core\Block\Adminhtml\Config;


use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Serialize\SerializerInterface;

class License extends Field
{
    protected $_template = 'MageMe_Core::config/license.phtml';

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    protected $fieldConfig = [];

    /**
     * License constructor.
     * @param SerializerInterface $serializer
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        SerializerInterface $serializer,
        Context             $context,
        array               $data = [])
    {
        parent::__construct($context, $data);
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function render(AbstractElement $element): string
    {
        $this->fieldConfig = $element->getData('field_config');

        return parent::render($element);
    }

    public function getJsonConfig()
    {
        return $this->serializer->serialize([
            'isActive' => $this->isActive(),
            'activateUrl' => $this->getActivateUrl(),
            'deactivateUrl' => $this->getDeactivateUrl(),
            'moduleId' => $this->fieldConfig['module_id'] ?? '',
            'moduleName' => $this->fieldConfig['module_name'] ?? ''
        ]);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        $module = $this->fieldConfig['module_id'] ?? '';
        return (bool)$this->_scopeConfig->getValue($module . '/license/active');
    }

    /**
     * @return string
     */
    public function getActivateUrl(): string
    {
        return $this->getUrl('core/license/activate');
    }

    /**
     * @return string
     */
    public function getDeactivateUrl(): string
    {
        return $this->getUrl('core/license/deactivate');
    }

    /**
     * @return string
     */
    public function getModuleId(): string
    {
        return $this->fieldConfig['module_id'] ?? '';
    }

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return parent::_getElementHtml($element) . $this->_toHtml();
    }
}
