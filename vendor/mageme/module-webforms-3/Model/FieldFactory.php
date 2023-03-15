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

namespace MageMe\WebForms\Model;


use Exception;
use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Config\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

class FieldFactory
{
    protected const DEFAULT_FIELD = 'text';

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Config
     */
    protected $config;

    /**
     * FieldFactory constructor.
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Config                 $config
    )
    {
        $this->objectManager = $objectManager;
        $this->config        = $config;
    }

    /**
     * Factory method
     *
     * @param string $fieldType
     * @param array $data
     * @return FieldInterface
     * @throws LocalizedException
     */
    public function create(string $fieldType = self::DEFAULT_FIELD, array $data = [])
    {
        $fieldTypes  = $this->config->getFieldTypes();
        $isLogicType = false;
        if (isset($fieldTypes[$fieldType])) {
            $className   = $fieldTypes[$fieldType]['model'];
            $isLogicType = $fieldTypes[$fieldType]['logic'];
        } else {
            $className = str_contains($fieldType, '\\') ? $fieldType : '';
        }
        if (!$className) {
            $className   = $fieldTypes[self::DEFAULT_FIELD]['model'];
            $isLogicType = $fieldTypes[self::DEFAULT_FIELD]['logic'];
        }
        try {
            $field = $this->objectManager->create($className, $data);
            $field->setData(FieldInterface::IS_LOGIC_TYPE, $isLogicType);
            $field->setData(FieldInterface::TYPE, $fieldType);
        } catch (Exception $exception) {
            throw new LocalizedException(
                __('(%1) : %2', self::class, $exception->getMessage())
            );
        }
        if (!$field instanceof FieldInterface) {
            throw new LocalizedException(
                __('(%1) class doesn\'t implement %2', $className, FieldInterface::class)
            );
        }
        return $field;
    }
}
