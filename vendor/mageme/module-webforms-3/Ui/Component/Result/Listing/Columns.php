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

namespace MageMe\WebForms\Ui\Component\Result\Listing;


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\Ui\FieldResultListingColumnInterface;
use MageMe\WebForms\Config\Options\Result\ApprovalStatus as Status;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\DataType;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\Filter;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\Store;

class Columns extends \Magento\Ui\Component\Listing\Columns
{
    /**
     * Default columns max order
     */
    const DEFAULT_COLUMNS_MAX_ORDER = 100;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var UiComponentFactory
     */
    protected $componentFactory;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * Columns constructor.
     * @param AuthorizationInterface $authorization
     * @param FieldRepositoryInterface $fieldRepository
     * @param FormRepositoryInterface $formRepository
     * @param Status $status
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param UiComponentFactory $componentFactory
     * @param EventManagerInterface $eventManager
     * @param ObjectManagerInterface $objectManager
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     */
    public function __construct(
        AuthorizationInterface $authorization,
        FieldRepositoryInterface $fieldRepository,
        FormRepositoryInterface $formRepository,
        Status $status,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        UiComponentFactory $componentFactory,
        EventManagerInterface $eventManager,
        ObjectManagerInterface $objectManager,
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->objectManager    = $objectManager;
        $this->eventManager     = $eventManager;
        $this->componentFactory = $componentFactory;
        $this->urlBuilder       = $urlBuilder;
        $this->request          = $request;
        $this->status           = $status;
        $this->formRepository   = $formRepository;
        $this->fieldRepository  = $fieldRepository;
        $this->authorization    = $authorization;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function prepare()
    {
        $storeId = $this->request->getParam(Store::ENTITY);
        $formId  = (int)$this->request->getParam(FieldInterface::FORM_ID);
        if ($formId) {
            $this->prepareColumns($formId, $storeId);
        }
        parent::prepare();
    }

    /**
     * @param int $formId
     * @param int|null $storeId
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function prepareColumns(int $formId, ?int $storeId)
    {
        $form = $this->formRepository->getById($formId, $storeId);
        if ($form->getIsApprovalControlsEnabled()) {
            $config    = [
                'name' => 'approved',
                'sortOrder' => 40,
                'label' => __('Status'),
                'dataType' => DataType::SELECT,
                'filter' => Filter::SELECT,
                'sortable' => true,
                'disableAction' => true,
                'options' => $this->status->toOptionArray(),
                'component' => 'MageMe_WebForms/js/grid/columns/status',
                'isActionsAllowed' => $this->authorization->isAllowed('MageMe_WebForms::reply'),
                'url' => $this->urlBuilder->getUrl('*/*/setStatus', ['_current' => true])
            ];
            $arguments = [
                'data' => [
                    'config' => $config,
                ],
                'context' => $this->getContext(),
            ];
            $column    = $this->componentFactory->create($config['name'], 'column', $arguments);
            $column->prepare();
            $this->addComponent($config['name'], $column);
        }

        /** @var FieldInterface[] $fields */
        $fields          = $this->fieldRepository->getListByWebformId($formId, $storeId)->getItems();
        $columnSortOrder = self::DEFAULT_COLUMNS_MAX_ORDER;
        foreach ($fields as $field) {
            $fieldUi = $field->getFieldUi();
            if (!($fieldUi instanceof FieldResultListingColumnInterface)) {
                continue;
            }
            $config = $fieldUi->getResultListingColumnConfig($columnSortOrder);
            $columnSortOrder++;
            if (count($fields) > ResultInterface::MAX_JOIN_FIELDS) {
                $config['filter']   = false;
                $config['sortable'] = false;
            }
            $arguments = [
                'data' => [
                    'config' => $config,
                    'field' => $field,
                    'name' => $config['name'],
                ],
                'context' => $this->getContext(),
            ];

            $columnConfig = new DataObject(['class' => $config['class'], 'arguments' => $arguments]);

            $this->eventManager->dispatch('webforms_ui_component_result_listing_columns_prepare_config',
                ['field' => $field, 'column_config' => $columnConfig]);

            /**
             * @noinspection PhpUndefinedMethodInspection
             * @noinspection PhpPossiblePolymorphicInvocationInspection
             */
            $column = $this->objectManager->create($columnConfig->getClass(), $columnConfig->getArguments());
            $column->prepare();

            $this->addComponent($config['name'], $column);
        }
    }
}
