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

namespace MageMe\WebForms\Ui\Field;


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\Ui\FieldUiInterface;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\BodyTmpl;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\Component;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\DataType;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\Filter;
use MageMe\WebForms\Ui\Component\Result\Listing\Column\Field as ColumnField;

abstract class AbstractField implements FieldUiInterface
{
    /** @var FieldInterface */
    protected $field;

    /**
     * @inheritDoc
     */
    public function getUiMeta(string $prefix = ''): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getLogicValueMeta(): array
    {
        return [];
    }

    /**
     * @param int $sortOrder
     * @return array
     */
    public function getResultListingColumnConfig(int $sortOrder): array
    {
        return $this->getDefaultUIResultColumnConfig($sortOrder);
    }

    /**
     * Get default config for initialize column in Result UI listing
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getDefaultUIResultColumnConfig(int $sortOrder): array
    {
        return [
            'name' => 'field_' . $this->getField()->getId(),
            'sortOrder' => $sortOrder,
            'label' => $this->getField()->getResultLabel() ?: $this->getField()->getName(),
            'dataType' => DataType::TEXT,
            'filter' => Filter::TEXT,
            'sortable' => true,
            'component' => Component::COLUMN,
            'bodyTmpl' => BodyTmpl::HTML,
            'class' => ColumnField::class,
        ];
    }

    /**
     * @return FieldInterface
     */
    public function getField(): FieldInterface
    {
        return $this->field;
    }

    /**
     * @param FieldInterface $field
     * @return $this
     */
    public function setField(FieldInterface $field): FieldUiInterface
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @param ResultInterface|null $result
     * @return array
     */
    public function getResultAdminFormConfig(ResultInterface $result = null): array
    {
        return $this->getDefaultResultAdminFormConfig();
    }

    /**
     * Get default config for result admin form
     *
     * @return array
     */
    protected function getDefaultResultAdminFormConfig(): array
    {
        return [
            'name' => 'field[' . $this->getField()->getId() . ']',
            'label' => $this->getField()->getName(),
            'container_id' => 'field_' . $this->getField()->getId() . '_container',
            'required' => $this->getField()->getIsRequired(),
            'type' => 'text'
        ];
    }
}
