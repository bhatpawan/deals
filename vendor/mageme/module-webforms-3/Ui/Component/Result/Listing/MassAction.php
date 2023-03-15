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

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Config\Options\Result\ApprovalStatus;
use MageMe\WebForms\Ui\Component\Common\Listing\MassAction\AbstractMassAction;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class MassAction extends AbstractMassAction
{
    /**
     * @inheritdoc
     */
    protected $actionsAcl = [
        'status' => 'MageMe_WebForms::reply',
        'delete' => 'MageMe_WebForms::edit_result',
        'is_read' => 'MageMe_WebForms::reply',
        'is_replied' => 'MageMe_WebForms::reply',
    ];

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
     * @var ApprovalStatus
     */
    protected $approvalStatus;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @param FormRepositoryInterface $formRepository
     * @param ApprovalStatus $approvalStatus
     * @param UiComponentFactory $componentFactory
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     * @param AuthorizationInterface $authorization
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     */
    public function __construct(
        FormRepositoryInterface $formRepository,
        ApprovalStatus          $approvalStatus,
        UiComponentFactory      $componentFactory,
        UrlInterface            $urlBuilder,
        RequestInterface        $request,
        AuthorizationInterface  $authorization,
        ContextInterface        $context,
        array                   $components = [],
        array                   $data = []
    )
    {
        parent::__construct($authorization, $context, $components, $data);
        $this->request          = $request;
        $this->urlBuilder       = $urlBuilder;
        $this->componentFactory = $componentFactory;
        $this->approvalStatus   = $approvalStatus;
        $this->formRepository   = $formRepository;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function prepare()
    {
        $formId = (int)$this->request->getParam(FormInterface::ID);

        if ($formId) {
            $form = $this->formRepository->getById($formId);
            if ($form->getIsApprovalControlsEnabled()) {
                $config = [
                    'type' => 'status',
                    'label' => __('Change Status'),
                    'actions' => []
                ];

                foreach ($this->approvalStatus->toOptionArray() as $option) {
                    $config['actions'][] = [
                        'type' => 'status' . $option['value'],
                        'url' => $this->urlBuilder->getUrl('webforms/result/massStatus',
                            ['_current' => true, 'status' => $option['value']]),
                        'label' => $option['label']
                    ];
                }

                $arguments = [
                    'data' => [
                        'config' => $config,
                    ],
                    'context' => $this->getContext(),
                ];

                $actionComponent = $this->componentFactory->create('status', 'action', $arguments);
                $this->addComponent('status', $actionComponent);
            }
        }

        parent::prepare();
    }

}
