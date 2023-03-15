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

namespace MageMe\WebForms\Ui\Component\Logic\Listing\Column;


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\LogicInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Target extends Column
{
    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * Target constructor.
     * @param FieldRepositoryInterface $fieldRepository
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        FieldRepositoryInterface $fieldRepository,
        ContextInterface         $context,
        UiComponentFactory       $uiComponentFactory,
        array                    $components = [],
        array                    $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->fieldRepository = $fieldRepository;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'][0])) {
            $fieldName         = $this->getData('name');
            $fieldId           = $dataSource['data']['items'][0][LogicInterface::FIELD_ID];
            $storeId           = $this->context->getFilterParam('store_id');
            $webform           = $this->fieldRepository->getById($fieldId, $storeId)->getForm();
            $fieldsToFieldsets = $webform->getFieldsToFieldsets(true);
            foreach ($dataSource['data']['items'] as &$item) {
                $value   = $item[$fieldName];
                $options = [];
                foreach ($fieldsToFieldsets as $fieldsetId => $fieldset) {
                    $fieldOptions = [];

                    /** @var FieldInterface $field */
                    foreach ($fieldset['fields'] as $field) {
                        if (is_array($value) && in_array('field_' . $field->getId(), $value)) {
                            $fieldOptions[] = $field->getName();
                        }
                    }

                    if ($fieldsetId) {
                        if (is_array($value) && in_array('fieldset_' . $fieldsetId, $value)) {
                            $options[] = $fieldset['name'] . ' [' . __('Fieldset') . ']';
                        }
                        if (count($fieldOptions)) {
                            $options[] = '<b>' . $fieldset['name'] . '</b><br>&nbsp;&nbsp;&nbsp;&nbsp;' . implode('<br>&nbsp;&nbsp;&nbsp;&nbsp;',
                                    $fieldOptions);
                        }
                    } else {
                        foreach ($fieldOptions as $opt) {
                            $options[] = $opt;
                        }
                    }
                }
                if (is_array($value) && in_array('submit', $value)) {
                    $options[] = '[' . strtoupper(__('Submit')) . ']';
                }
                $item[$fieldName] = implode('<br>', $options);
            }
        }
        return $dataSource;
    }

}
