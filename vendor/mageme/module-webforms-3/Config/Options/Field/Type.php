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

namespace MageMe\WebForms\Config\Options\Field;


use MageMe\WebForms\Config\Config;
use Magento\Framework\Data\OptionSourceInterface;

class Type implements OptionSourceInterface
{

    /**
     * @var Config
     */
    protected $_config;

    /**
     * Type constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    )
    {
        $this->_config = $config;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->_config->getFieldTypes() as $key => $data) {
            $options[] = [
                'label' => $data['label'],
                'value' => $key
            ];
        }
        return $options;
    }
}
