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

namespace MageMe\WebForms\Ui\Component\Result\Listing\Column;

use MageMe\WebForms\Api\FieldRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class File
 * @package MageMe\WebForms\Ui\Component\Result\Listing\Column
 */
class Field extends Column
{
    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * File constructor.
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
     * @param array $dataSource
     * @return array
     * @throws LocalizedException
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = (string)$this->getData('name');
            $fieldId   = str_replace('field_', '', $fieldName);
            $field     = $this->fieldRepository->getById($fieldId);
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName])) {
                    $html             = $field->getValueForResultAdminGrid($item[$fieldName],
                        ['result_id' => $item['result_id']]);
                    $item[$fieldName] = $html;
                } else {
                    $item[$fieldName] = '';
                }
            }
        }

        return $dataSource;
    }

}
