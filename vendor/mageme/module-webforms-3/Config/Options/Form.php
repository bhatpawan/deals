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

namespace MageMe\WebForms\Config\Options;

use MageMe\WebForms\Model\ResourceModel\Form\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 *
 */
class Form implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var CollectionFactory
     */
    protected $_formCollectionFactory;

    /**
     * @param CollectionFactory $formCollectionFactory
     */
    public function __construct(
        CollectionFactory $formCollectionFactory
    )
    {
        $this->_formCollectionFactory = $formCollectionFactory;
    }

    /**
     * To option array
     *
     * @param bool $default
     * @return array
     */
    public function toOptionArray(bool $default = false): array
    {
        $options = [];
        $forms   = $this->_formCollectionFactory->create();
        foreach ($forms as $form) {
            $options[] = [
                'label' => $form->getName(),
                'value' => $form->getId(),
            ];
        }
        $this->options = $options;
        return $this->options;
    }

    /**
     * @return array
     */
    public function toGridOptionArray(): array
    {
        $options = [];
        $forms   = $this->_formCollectionFactory->create();
        foreach ($forms as $form) {
            $options[$form->getId()] = $form->getName();
        }
        $this->options = $options;
        return $this->options;
    }

}
