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
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Action extends Column
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
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @param AuthorizationInterface $authorization
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param ResultRepositoryInterface $resultRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        AuthorizationInterface    $authorization,
        ContextInterface          $context,
        UiComponentFactory        $uiComponentFactory,
        UrlInterface              $urlBuilder,
        ResultRepositoryInterface $resultRepository,
        array                     $components = [],
        array                     $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder       = $urlBuilder;
        $this->resultRepository = $resultRepository;
        $this->authorization    = $authorization;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as &$item) {
                $item = $this->prepareItem($item);
            }
        }
        return $dataSource;
    }

    /**
     * Prepare actions
     *
     * @param $item
     * @return array
     */
    public function prepareItem($item)
    {
        if ($this->authorization->isAllowed('MageMe_WebForms::edit_result')) {
            if (!$item[ResultInterface::IS_READ]) {
                $item[$this->getData('name')]['mark_as_read'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'webforms/result/markRead',
                        [ResultInterface::ID => $item[ResultInterface::ID], '_current' => true]
                    ),
                    'label' => __('Mark as Read'),
                    'hidden' => false,
                    'sortOrder' => 1,
                ];
            }

            $item[$this->getData('name')]['edit'] = [
                'href' => $this->urlBuilder->getUrl(
                    'webforms/result/edit',
                    [ResultInterface::ID => $item[ResultInterface::ID], '_current' => true]
                ),
                'label' => __('Edit'),
                'hidden' => false,
                'sortOrder' => 10,
            ];
        }

        if ($this->authorization->isAllowed('MageMe_WebForms::reply')) {
            $item[$this->getData('name')]['reply'] = [
                'href' => $this->urlBuilder->getUrl(
                    'webforms/result/reply',
                    [ResultInterface::ID => $item[ResultInterface::ID], '_current' => true]
                ),
                'label' => __('Reply'),
                'hidden' => false,
                'sortOrder' => 20,
            ];
        }

        $indexField = $this->getData('config/indexField') ?: 'entity_id';
        try {
            $result = $this->resultRepository->getById($item[$indexField]);
            if (count($result->getFiles())) {
                $item[$this->getData('name')]['exportFiles'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'webforms/result/exportFiles',
                        [ResultInterface::ID => $item[ResultInterface::ID], '_current' => true]
                    ),
                    'label' => __('Download Files'),
                    'hidden' => false,
                    'sortOrder' => 30,
                ];
            }
        } catch (LocalizedException $exception) {
            return $item;
        }
        return $item;
    }
}
