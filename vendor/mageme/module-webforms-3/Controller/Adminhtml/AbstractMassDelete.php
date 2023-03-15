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
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class MassDelete
 */
abstract class AbstractMassDelete extends AbstractMassAction
{
    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * AbstractMassDelete constructor.
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
                    $this->deleteItem($id);
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', count($Ids))
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
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     * @noinspection PhpDocRedundantThrowsInspection
     */
    protected function deleteItem(int $id)
    {
        $this->repository->delete($this->repository->getById($id));
    }
}