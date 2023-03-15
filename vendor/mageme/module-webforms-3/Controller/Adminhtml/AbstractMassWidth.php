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

namespace MageMe\WebForms\Controller\Adminhtml;


use Exception;
use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\FieldsetRepositoryInterface;
use MageMe\WebForms\Api\RepositoryInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

abstract class AbstractMassWidth extends AbstractMassAction
{
    /**
     * @var RepositoryInterface|FieldRepositoryInterface|FieldsetRepositoryInterface
     */
    protected $repository;

    /**
     * AbstractMassWidth constructor.
     * @param RepositoryInterface $repository
     * @param AccessHelper $accessHelper
     * @param Context $context
     */
    public function __construct(
        RepositoryInterface $repository,
        AccessHelper        $accessHelper,
        Context             $context
    )
    {
        parent::__construct($accessHelper, $context);
        $this->repository = $repository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $Ids     = $this->getIds();
        $widthLg = $this->getRequest()->getParam(FieldsetInterface::WIDTH_PROPORTION_LG) ?
            $this->getRequest()->getParam(FieldsetInterface::WIDTH_PROPORTION_LG) : false;
        $widthMd = $this->getRequest()->getParam(FieldsetInterface::WIDTH_PROPORTION_MD) ?
            $this->getRequest()->getParam(FieldsetInterface::WIDTH_PROPORTION_MD) : false;
        $widthSm = $this->getRequest()->getParam(FieldsetInterface::WIDTH_PROPORTION_SM) ?
            $this->getRequest()->getParam(FieldsetInterface::WIDTH_PROPORTION_SM) : false;
        if (empty($Ids)) {
            $this->messageManager->addErrorMessage(__('Please select item(s).'));
        } else {
            try {
                foreach ($Ids as $id) {
                    $this->setItemWidth($id, $widthLg, $widthMd, $widthSm);
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been updated.', count($Ids))
                );
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath(static::REDIRECT_URL, $this->redirect_params);
    }

    /**
     * Set item width
     *
     * @param int $id
     * @param string|bool $widthLg
     * @param string|bool $widthMd
     * @param string|bool $widthSm
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    protected function setItemWidth(int $id, $widthLg, $widthMd, $widthSm)
    {
        $item = $this->repository->getById($id);
        if ($widthLg) {
            $item->setWidthProportionLg($widthLg);
        }
        if ($widthMd) {
            $item->setWidthProportionMd($widthMd);
        }
        if ($widthSm) {
            $item->setWidthProportionSm($widthSm);
        }
        $this->repository->save($item);
    }
}