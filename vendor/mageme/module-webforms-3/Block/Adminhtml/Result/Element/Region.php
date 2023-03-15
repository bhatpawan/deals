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

use Magento\Directory\Helper\Data;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;

class Region extends AbstractElement
{
    /**
     *
     */
    const TYPE = 'region';

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Region constructor.
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Factory           $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper           $escaper,
        Data              $helper,
                          $data = []
    )
    {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function getElementHtml(): string
    {
        $regionInput = sprintf('<input id="%s" name="%s" %s style="display: none" value="%s"/>',
            $this->getHtmlId() . 'region',
            $this->getName() . '[region]',
            $this->serialize($this->getHtmlAttributes()),
            htmlentities((string)$this->getData('region'))
        );

        $regionList = sprintf('<select id="%s" name="%s" %s style="display:none;"><option value="">%s</option></select>',
            $this->getHtmlId() . 'region_id',
            $this->getName() . '[region_id]',
            $this->serialize($this->getHtmlAttributes()),
            __('Please select a region, state or province.')
        );

        return $regionInput . $regionList . $this->_getScript();
    }

    /**
     * @return string
     */
    private function _getScript(): string
    {
        $countryFieldId                  = '#' . $this->getData('country_field_id');
        $config['optionalRegionAllowed'] = true;
        $config['regionListId']          = '#' . $this->getHtmlId() . 'region_id';
        $config['regionInputId']         = '#' . $this->getHtmlId() . 'region';
        $config['regionJson']            = $this->helper->getRegionData();
        $config['isRegionRequired']      = (bool)$this->getData('required');
        $config['currentRegion']         = $this->getData('region_id');

        return sprintf('<script type="text/x-magento-init">{"%s": {"webformsRegion": %s}}</script>',
            $countryFieldId,
            json_encode($config)
        );
    }

}
