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

namespace MageMe\WebForms\Ui\Component\QuickresponseCategory\Form;


use MageMe\WebForms\Api\Data\QuickresponseCategoryInterface;
use MageMe\WebForms\Api\QuickresponseCategoryRepositoryInterface;
use MageMe\WebForms\Model\ResourceModel\QuickresponseCategory as QuickresponseCategoryResource;
use MageMe\WebForms\Model\ResourceModel\QuickresponseCategory\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var QuickresponseCategoryResource
     */
    protected $quickresponseCategoryResource;
    /**
     * @var QuickresponseCategoryRepositoryInterface
     */
    protected $quickresponseCategoryRepository;

    /**
     * DataProvider constructor.
     * @param QuickresponseCategoryRepositoryInterface $quickresponseCategoryRepository
     * @param QuickresponseCategoryResource $quickresponseCategoryResource
     * @param RequestInterface $request
     * @param CollectionFactory $collectionFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        QuickresponseCategoryRepositoryInterface $quickresponseCategoryRepository,
        QuickresponseCategoryResource            $quickresponseCategoryResource,
        RequestInterface                         $request,
        CollectionFactory                        $collectionFactory,
        string                                   $name,
        string                                   $primaryFieldName,
        string                                   $requestFieldName,
        array                                    $meta = [],
        array                                    $data = [])
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection                      = $collectionFactory->create();
        $this->request                         = $request;
        $this->quickresponseCategoryResource   = $quickresponseCategoryResource;
        $this->quickresponseCategoryRepository = $quickresponseCategoryRepository;
    }

    /**
     * @inheritdoc
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getData(): array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $quickresponseCategoryId = (int)$this->request->getParam(QuickresponseCategoryInterface::ID);
        if (!$quickresponseCategoryId) {
            $this->loadedData[''] = [
                QuickresponseCategoryInterface::POSITION => $this->quickresponseCategoryResource->getNextPosition()
            ];
            return $this->loadedData;
        }
        $quickresponseCategory                             = $this->quickresponseCategoryRepository->getById($quickresponseCategoryId);
        $this->loadedData[$quickresponseCategory->getId()] = $quickresponseCategory->getData();
        return $this->loadedData;
    }
}
