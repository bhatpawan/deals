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

namespace MageMe\WebForms\Ui\Component\Fieldset\Listing;


use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Ui\Component\Common\Listing\AbstractStoreDataProvider;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class DataProvider extends AbstractStoreDataProvider
{
    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @inheritdoc
     */
    protected $columnsName = 'fieldset_columns';

    /**
     * @inheritdoc
     */
    protected $storeFields = [
        FieldsetInterface::NAME
    ];

    /**
     * DataProvider constructor.
     * @param FormRepositoryInterface $formRepository
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        FormRepositoryInterface $formRepository,
        string                  $name,
        string                  $primaryFieldName,
        string                  $requestFieldName,
        ReportingInterface      $reporting,
        SearchCriteriaBuilder   $searchCriteriaBuilder,
        RequestInterface        $request,
        FilterBuilder           $filterBuilder,
        array                   $meta = [],
        array                   $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request,
            $filterBuilder, $meta, $data);
        $this->formRepository = $formRepository;
    }

    /**
     * @inheritdoc
     */
    public function getData(): array
    {
        $data                                    = parent::getData();
        $data[FormInterface::IS_WIDTH_LG_SHOWED] = false;
        $data[FormInterface::IS_WIDTH_MD_SHOWED] = false;
        $data[FormInterface::IS_WIDTH_SM_SHOWED] = false;
        $formId                                  = (int)$this->request->getParam(FieldsetInterface::FORM_ID);
        if ($formId) {
            try {
                $form                                    = $this->formRepository->getById($formId);
                $data[FormInterface::IS_WIDTH_LG_SHOWED] = $form->getIsWidthLgShowed();
                $data[FormInterface::IS_WIDTH_MD_SHOWED] = $form->getIsWidthMdShowed();
                $data[FormInterface::IS_WIDTH_SM_SHOWED] = $form->getIsWidthSmShowed();
            } catch (NoSuchEntityException $e) {
            }
        }
        return $data;
    }
}
