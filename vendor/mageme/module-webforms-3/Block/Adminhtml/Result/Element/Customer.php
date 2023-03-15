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

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\View\Layout;

/**
 *
 */
class Customer extends AbstractElement
{
    /**
     * @var Layout
     */
    protected $layout;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param Layout $layout
     * @param array $data
     */
    public function __construct(
        Factory           $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper           $escaper,
        Layout            $layout,
                          $data = []
    )
    {
        $this->layout = $layout;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * @return string
     */
    public function getElementHtml(): string
    {
        $config = [
            'value' => is_string($this->getValue()) ? htmlentities((string)$this->getValue()) : $this->getValue()
        ];
        $html   = $this->layout->createBlock(\MageMe\WebForms\Block\Adminhtml\Result\Autocomplete\Customer::class,
            $this->getName(), ['data' => $config])->toHtml();
        $html   .= $this->getAfterElementHtml();

        return $html;
    }
}
