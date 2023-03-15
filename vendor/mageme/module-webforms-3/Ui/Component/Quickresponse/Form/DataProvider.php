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

namespace MageMe\WebForms\Ui\Component\Quickresponse\Form;


use MageMe\WebForms\Api\Data\QuickresponseInterface;
use MageMe\WebForms\Api\QuickresponseRepositoryInterface;
use MageMe\WebForms\Model\ResourceModel\Quickresponse\CollectionFactory;
use Magento\Framework\App\RequestInterface;
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
     * @var QuickresponseRepositoryInterface
     */
    protected $quickresponseRepository;

    /**
     * DataProvider constructor.
     * @param QuickresponseRepositoryInterface $quickresponseRepository
     * @param RequestInterface $request
     * @param CollectionFactory $collectionFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        QuickresponseRepositoryInterface $quickresponseRepository,
        RequestInterface                 $request,
        CollectionFactory                $collectionFactory,
        string                           $name,
        string                           $primaryFieldName,
        string                           $requestFieldName,
        array                            $meta = [],
        array                            $data = [])
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection              = $collectionFactory->create();
        $this->request                 = $request;
        $this->quickresponseRepository = $quickresponseRepository;
    }

    /**
     * @inheritdoc
     * @throws NoSuchEntityException
     */
    public function getData(): ?array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $quickresponseId = (int)$this->request->getParam(QuickresponseInterface::ID);
        if (!$quickresponseId) {
            return $this->loadedData;
        }
        $quickresponse                             = $this->quickresponseRepository->getById($quickresponseId);
        $this->loadedData[$quickresponse->getId()] = $quickresponse->getData();
        return $this->loadedData;
    }
}
