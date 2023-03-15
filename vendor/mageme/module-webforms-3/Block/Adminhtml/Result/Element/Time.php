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


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 *
 */
class Time extends AbstractElement
{
    /**
     *
     */
    const TYPE = 'time';
    /**
     * @var FieldRepositoryInterface
     */
    private $fieldRepository;

    /**
     * Time constructor.
     * @param FieldRepositoryInterface $fieldRepository
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        FieldRepositoryInterface $fieldRepository,
        Factory                  $factoryElement,
        CollectionFactory        $factoryCollection,
        Escaper                  $escaper,
                                 $data = []
    )
    {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->fieldRepository = $fieldRepository;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getElementHtml(): string
    {
        $fieldId = (int)$this->getData(FieldInterface::ID);
        $field   = $this->fieldRepository->getById($fieldId);
        $value   = $this->getValue();
        if (is_string($value) && !empty($value)) {
            $value = explode(":", $value);
        }
        $vHour = false;
        $vMinute = false;
        if (is_array($value) && isset($value[0], $value[1])) {
            $vHour = $value[0];
            $vMinute = $value[1];
        }
        $hours   = $this->getSelect(
            $this->getHtmlId() . '_hours',
            __('Hours'),
            htmlspecialchars((string)$field->getCustomAttributes()),
            $this->getOptionsHtml($field->getAvailableHours(), $vHour)
        );
        $minutes = $this->getSelect(
            $this->getHtmlId() . '_minutes',
            __('Minutes'),
            htmlspecialchars((string)$field->getCustomAttributes()),
            $this->getOptionsHtml($field->getAvailableMinutes(), $vMinute)
        );
        $input   = $this->getInput(
            $this->getHtmlId(),
            $this->getName(),
            htmlspecialchars((string)$field->getCustomAttributes(),
                $this->getRequired())
        );
        $html    = sprintf('<div class="webforms-time" aria-label="%s" role="group">%s%s%s%s</div>',
            __("Time"),
            $hours,
            '<span class="time-separator">&nbsp;:&nbsp;</span>',
            $minutes,
            $input
        );
        $html    .= $this->getScript($this->getHtmlId());
        $html    .= $this->getAfterElementHtml();
        return $html;
    }

    /**
     * @param $id
     * @param $label
     * @param $attr
     * @param $options
     * @return string
     */
    private function getSelect($id, $label, $attr, $options): string
    {
        return sprintf('<select id="%s"
                            class="hours"
                            aria-label="%s"
                            "%s"
                    >
                        "%s"
                    </select>',
            $id,
            $label,
            $attr,
            $options
        );
    }

    /**
     * @param array $options
     * @param mixed $value
     * @return string
     */
    private function getOptionsHtml(array $options, $value): string
    {
        $html = '<option value=""></option>';
        foreach ($options as $option) {
            $html .= sprintf(
                '<option value="%s" %s>%s</option>',
                $option,
                $value === $option ? 'selected' : '',
                $option
            );
        }
        return $html;
    }

    /**
     * @param $id
     * @param $name
     * @param $attr
     * @return string
     */
    private function getInput($id, $name, $attr): string
    {
        return sprintf('<input id="%s"
                                      name="%s"
                                      type="hidden"
                                      class="%s"
                                      style="display: none"
                                      data-validate="{\'time\':true}"
                                      data-msg-time="%s"
                                      %s
                                      />',
            $id,
            $name,
            false ? 'required-entry _required' : '',
            __('Please select a valid time'),
            $attr
        );
    }

    /**
     * @param $id
     * @return string
     */
    private function getScript($id): string
    {
        return sprintf('<script>
                    require(["jquery"], function ($) {
                        var hours = $("%s"),
                            minutes = $("%s"),
                            time = $("%s");
                        function setTime() {
                            var hourVal = hours.val(),
                                minuteVal = minutes.val(),
                                timeVal = hourVal + ":" + minuteVal;
                            time.val(timeVal.length === 1 ? "" : timeVal);
                        }
                        hours.change(setTime);
                        minutes.change(setTime);
                        setTime();
                    });
                </script>',
            '#' . $id . '_hours',
            '#' . $id . '_minutes',
            '#' . $id
        );
    }

}
