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

namespace MageMe\WebForms\Block\Widget;

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Block\Result\Rating;
use MageMe\WebForms\Model;
use MageMe\WebForms\Model\ResourceModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Pager;
use Magento\Widget\Block\BlockInterface;

/**
 *
 */
class Result extends Template implements BlockInterface
{
    /**
     * @var
     */
    protected $resultsCollection;

    /**
     * @var ResourceModel\Result\CollectionFactory
     */
    protected $resultCollectionFactory;

    /**
     * @var Pager
     */
    protected $htmlPagerBlock;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var ResourceModel\FileDropzone\CollectionFactory
     */
    protected $fileCollectionFactory;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @param FormRepositoryInterface $formRepository
     * @param ResourceModel\FileDropzone\CollectionFactory $fileCollectionFactory
     * @param ResourceModel\Result\CollectionFactory $resultCollectionFactory
     * @param Pager $htmlPagerBlock
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        FormRepositoryInterface $formRepository,
        Model\ResourceModel\FileDropzone\CollectionFactory $fileCollectionFactory,
        ResourceModel\Result\CollectionFactory             $resultCollectionFactory,
        Pager                                              $htmlPagerBlock,
        Template\Context $context,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->htmlPagerBlock          = $htmlPagerBlock;
        $this->resultCollectionFactory = $resultCollectionFactory;
        $this->fileCollectionFactory   = $fileCollectionFactory;
        $this->formRepository   = $formRepository;
    }

    /**
     * @return ResourceModel\FileDropzone\Collection
     */
    public function getFileCollection(): ResourceModel\FileDropzone\Collection
    {
        return $this->fileCollectionFactory->create();
    }

    public function getFormId(): int
    {
        return (int)$this->getData(ResultInterface::FORM_ID);
    }

    /**
     * @return int
     */
    public function getImageWidth(): int
    {
        return (int)$this->getData('image_width') ?: 200;
    }

    /**
     * @return int
     */
    public function getImageHeight(): int
    {
        return (int)$this->getData('image_height') ?: 200;
    }

    /**
     * @return bool
     */
    public function getImageLink(): bool
    {
        return (bool)$this->getData('image_link');
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getStoreId(): array
    {
        $storeId = $this->getData('store_id') ?? $this->_storeManager->getStore()->getId();
        return explode(',', $storeId);
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareLayout(): Result
    {
        parent::_prepareLayout();

        if ($toolbar = $this->htmlPagerBlock) {
            $pSize = $this->getData('page_size');
            $toolbar->setAvailableLimit([$pSize => $pSize, $pSize * 2 => $pSize * 2, $pSize * 3 => $pSize * 3]);
            $toolbar->setCollection($this->getResultsCollection());
            /** @noinspection PhpParamsInspection */
            $this->addChild('toolbar', $toolbar);
        }
        if ($rating = $this->getLayout()->createBlock(Rating::class)) {
            $rating->setData(ResultInterface::FORM_ID, $this->getForm()->getId());
            $this->setChild('rating', $rating);
        }

        return $this;
    }

    /**
     * Get collection of approved submissions for current store view
     *
     * @return ResourceModel\Result\Collection
     * @throws NoSuchEntityException
     * @noinspection PhpParamsInspection
     */
    public function getResultsCollection(): ResourceModel\Result\Collection
    {
        if (null === $this->resultsCollection) {
            $this->resultsCollection = $this->resultCollectionFactory->create()->setLoadValues(true)
                ->addFilter(ResultInterface::STORE_ID, ['in' => $this->getStoreId()], 'public')
                ->addFilter(ResultInterface::FORM_ID, $this->getForm()->getId())
                ->addFilter(ResultInterface::APPROVED, 1)
                ->addOrder(ResultInterface::CREATED_AT, 'desc');
        }
        return $this->resultsCollection;
    }

    /**
     * @return FormInterface
     * @throws NoSuchEntityException
     */
    public function getForm(): FormInterface
    {
        if (null === $this->form) {
            $this->form = $this->formRepository->getById($this->getFormId());
        }
        return $this->form;
    }

    /**
     * @return string
     */
    protected function _toHtml(): string
    {
        $this->compatibilityPatch();
        return parent::_toHtml();
    }

    /**
     * Compatibility patch
     */
    protected function compatibilityPatch()
    {
        $template = str_replace('webforms/', '', $this->getTemplate(), $replaced);
        $changed  = (bool)$replaced;
        $template = str_replace('VladimirPopov', 'MageMe', $template, $replaced);
        $changed  = $changed || $replaced;
        if ($changed) {
            $this->setTemplate($template);
        }
    }
}
