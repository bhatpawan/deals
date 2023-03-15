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
use MageMe\WebForms\Api\RepositoryInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class MassDuplicate
 */
abstract class AbstractMassDuplicate extends AbstractMassAction
{
    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * AbstractMassDuplicate constructor.
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
     * @inheritDoc
     */
    public function execute()
    {
        $Ids = $this->getIds();
        if (empty($Ids)) {
            $this->messageManager->addErrorMessage(__('Please select item(s).'));
        } else {
            try {
                foreach ($Ids as $id) {
                    $this->duplicateItem($id);
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been duplicated.', count($Ids))
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
     * @param int $id
     * @return mixed
     * @throws NoSuchEntityException
     */
    protected function duplicateItem(int $id)
    {
        return $this->repository->getById($id)->duplicate();
    }
}
