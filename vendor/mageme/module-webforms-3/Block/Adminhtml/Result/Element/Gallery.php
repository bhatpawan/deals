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


use MageMe\WebForms\Model\Field\Type\Gallery as GalleryModel;
use MageMe\WebForms\Model\Field\Type\Select;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;

/**
 *
 */
class Gallery extends AbstractElement
{
    /**
     *
     */
    const TYPE = 'gallery';

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Factory           $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper           $escaper,
                          $data = []
    )
    {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType(self::TYPE);
        $this->setExtType(self::TYPE);
        if (isset($data['value'])) {
            $this->setValue($data['value']);
        }
    }

    /**
     * @return string
     */
    public function getElementHtml(): string
    {
        $isMultiselect = $this->getData(Select::IS_MULTISELECT);
        $elementName   = $isMultiselect ? $this->getName() . '[]' : $this->getName();
        $multiple      = $isMultiselect ? 'multiple' : '';
        $element       = sprintf('<select id="%s" name="%s" %s %s>%s</select>%s',
            $this->getHtmlId(),
            $elementName,
            $multiple,
            $this->serialize($this->getHtmlAttributes()),
            $this->getOptions(),
            $this->getAfterElementHtml()
        );

        return $element . $this->getImagePickerHtml();
    }

    /**
     * @return string
     */
    private function getOptions(): string
    {
        $html   = '<option value=""></option>';
        $images = $this->getData(GalleryModel::IMAGES);
        if (!is_array($images)) {
            return $html;
        }
        $width  = $this->getData(GalleryModel::IMAGE_WIDTH);
        $height = $this->getData(GalleryModel::IMAGE_HEIGHT);
        $values = $this->getValue();
        if (!is_array($values)) {
            $values = explode("\n", (string)$values);
        }
        foreach ($images as $image) {
            $selected = in_array($image['value_id'], $values) ? 'selected' : '';
            $html     .= '<option ' .
                'data-img-src="' . $image['url'] . '" ' .
                'data-img-alt="' . $image['label'] . '" ' .
                'data-img-width="' . $width . '" ' .
                'data-img-height="' . $height . '" ' .
                'value="' . $image['value_id'] . '" ' .
                $selected .
                '>' . $image['label'] . '</option>';
        }
        return $html;
    }

    /**
     * @return string
     */
    private function getImagePickerHtml(): string
    {
        return
            '<script>
            require(["jquery", "imagePicker"], function ($) {
                $("#' . $this->getHtmlId() . '").imagepicker({
                    hide_select : true,
                    show_label  : ' . json_encode((bool)$this->getData(GalleryModel::IS_LABELED)) . '
                })
            });
        </script>';
    }

}
