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

namespace MageMe\WebForms\Config;


use DOMNode;
use DOMXPath;
use Magento\Framework\Config\ConverterInterface;

class Converter implements ConverterInterface
{

    /**
     * @inheritDoc
     */
    public function convert($source): array
    {
        $xpath = new DOMXPath($source);
        return [
            'field_types' => $this->convertFieldTypes($xpath)
        ];
    }

    /**
     * Convert methods xml tree to array
     *
     * @param DOMXPath $xpath
     * @return array
     */
    protected function convertFieldTypes(DOMXPath $xpath): array
    {
        $fieldTypes = [];

        /** @var DOMNode $type */
        foreach ($xpath->query('/webforms/field_types/type') as $type) {
            $typeArray = [];

            $typeAttributes          = $type->attributes;
            $id                      = $typeAttributes->getNamedItem('id')->nodeValue;
            $typeArray['order']      = $typeAttributes->getNamedItem('order')->nodeValue;
            $typeArray['category']   = 'Other';
            $typeArray['value']      = null;
            $typeArray['logic']      = false;
            $typeArray['attributes'] = [];

            /** @var DOMNode $typeSubNode */
            foreach ($type->childNodes as $typeSubNode) {
                if ($typeSubNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                switch ($typeSubNode->nodeName) {
                    case 'label':
                        $typeArray['label'] = $typeSubNode->nodeValue;
                        break;
                    case 'category':
                        $typeArray['category'] = $typeSubNode->nodeValue;
                        break;
                    case 'value':
                        $typeArray['value'] = $typeSubNode->nodeValue;
                        break;
                    case 'logic':
                        $typeArray['logic'] = $typeSubNode->nodeValue;
                        break;
                    case 'model':
                        $typeArray['model'] = $typeSubNode->nodeValue;
                        break;
                    case 'attributes':

                        /** @var DOMNode $attributeNode */
                        foreach ($typeSubNode->childNodes as $attributeNode) {
                            if ($attributeNode->nodeType != XML_ELEMENT_NODE) {
                                continue;
                            }
                            $typeArray['attributes'][] = $attributeNode->attributes->getNamedItem('name')->nodeValue;
                        }
                        break;
                    default:
                        break;
                }
            }

            $fieldTypes[$id] = $typeArray;
        }
        uasort($fieldTypes, [$this, '_compareFieldTypes']);
        $config = [];
        foreach ($fieldTypes as $id => $data) {
            $config[$id] = [
                'label' => $data['label'],
                'model' => $data['model'],
                'category' => $data['category'],
                'value' => $data['value'],
                'logic' => $data['logic'],
                'attributes' => $data['attributes'],
            ];
        }
        return $config;
    }

    /**
     * Compare sort order of field types
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) Used in callback.
     *
     * @param array $left
     * @param array $right
     * @return int
     */
    private function _compareFieldTypes(array $left, array $right): int
    {
        return $left['order'] - $right['order'];
    }
}
