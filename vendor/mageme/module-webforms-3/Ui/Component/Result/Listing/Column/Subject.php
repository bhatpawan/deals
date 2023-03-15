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


use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Subject extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * Subject constructor.
     * @param ResultRepositoryInterface $resultRepository
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ResultRepositoryInterface $resultRepository,
        UrlInterface              $urlBuilder,
        ContextInterface          $context,
        UiComponentFactory        $uiComponentFactory,
        array                     $components = [],
        array                     $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder       = $urlBuilder;
        $this->resultRepository = $resultRepository;
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $field_id    = ResultInterface::ID;
            $fieldName   = $this->getData('name');
            $customer_id = $this->getData('customer_id');
            foreach ($dataSource['data']['items'] as & $item) {
                $id = $item[$field_id];
                try {
                    $result                = $this->resultRepository->getById($id);
                    $subject               = $result->getSubject();
                    $item['statusEnabled'] = $result->getForm()->getIsApprovalControlsEnabled();
                    $item['statusName']    = $result->getStatusName();
                    $item['statusClass']   = 'grid-status';
                    switch ($result->getApproved()) {
                        case '-1':
                            $item['statusClass'] .= ' notapproved';
                            break;
                        case '0':
                            $item['statusClass'] .= ' pending';
                            break;
                        case '1':
                            $item['statusClass'] .= ' approved';
                            break;
                        case '2':
                            $item['statusClass'] .= ' completed';
                            break;
                    }
                } catch (LocalizedException $exception) {
                    $subject = __('Could not load subject: %1', $exception->getMessage());
                }
                $url                 = $this->urlBuilder->getUrl('webforms/result/popup',
                    [ResultInterface::ID => $id, ResultInterface::CUSTOMER_ID => $customer_id]);
                $item[$fieldName]    = $subject;
                $item['subject-url'] = $url;
            }
        }

        return $dataSource;
    }
}
