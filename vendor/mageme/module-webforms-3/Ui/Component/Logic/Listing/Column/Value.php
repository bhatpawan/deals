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


use MageMe\WebForms\Api\FieldRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\Store;
use Magento\Ui\Component\Listing\Columns\Column;

class Value extends Column
{
    /**
     * @var ScopeConfigInterface
     */
    protected $request;

    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * Value constructor.
     * @param FieldRepositoryInterface $fieldRepository
     * @param RequestInterface $request
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        FieldRepositoryInterface $fieldRepository,
        RequestInterface         $request,
        ContextInterface         $context,
        UiComponentFactory       $uiComponentFactory,
        array                    $components = [],
        array                    $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->request         = $request;
        $this->fieldRepository = $fieldRepository;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');

            if (!empty($dataSource['data']['items'][0])) {
                $fieldId = $dataSource['data']['items'][0]['field_id'];
                $storeId = $this->request->getParam('store', Store::DEFAULT_STORE_ID);
                $field   = $this->fieldRepository->getById($fieldId, $storeId);
                $field->processColumnDataSource($dataSource);
            }

            foreach ($dataSource['data']['items'] as &$item) {
                if (is_array($item[$fieldName])) {
                    $item[$fieldName] = implode('<br>', $item[$fieldName]);
                }
            }
        }
        return $dataSource;
    }
}
