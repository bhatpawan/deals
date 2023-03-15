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

namespace MageMe\WebForms\Controller\Adminhtml\Form;

use Exception;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use Magento\Authorization\Model\ResourceModel\Rules as RulesResource;
use Magento\Authorization\Model\RulesFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Authorization\RoleLocator;

class Duplicate extends Action
{
    /**
     * @inheritdoc
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::add_form';

    /**
     * @var RoleLocator
     */
    protected $roleLocator;

    /**
     * @var RulesResource
     */
    protected $rulesResource;

    /**
     * @var RulesFactory
     */
    protected $rulesFactory;

    /**
     * @var AccessHelper
     */
    protected $accessHelper;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * Duplicate constructor.
     * @param FormRepositoryInterface $formRepository
     * @param AccessHelper $accessHelper
     * @param RulesFactory $rulesFactory
     * @param RulesResource $rulesResource
     * @param RoleLocator $roleLocator
     * @param Context $context
     */
    public function __construct(
        FormRepositoryInterface $formRepository,
        AccessHelper            $accessHelper,
        RulesFactory            $rulesFactory,
        RulesResource           $rulesResource,
        RoleLocator             $roleLocator,
        Context                 $context)
    {
        parent::__construct($context);
        $this->roleLocator    = $roleLocator;
        $this->rulesResource  = $rulesResource;
        $this->rulesFactory   = $rulesFactory;
        $this->accessHelper   = $accessHelper;
        $this->formRepository = $formRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $id             = (int)$this->getRequest()->getParam(FormInterface::ID);
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$id) {

            // display error message
            $this->messageManager->addErrorMessage(__('We can\'t find a form to duplicate.'));

            // go to grid
            return $resultRedirect->setPath('*/form/');
        }
        try {
            $newForm = $this->formRepository->getById($id)->duplicate();

            // update role permissions
            if (!$this->_authorization->isAllowed('Magento_Backend::all')) {
                $rules = $this->rulesFactory->create()->setData([
                    'role_id' => $this->roleLocator->getAclRoleId(),
                    'resource_id' => 'MageMe_WebForms::form' . $newForm->getId(),
                    'permission' => 'allow'
                ]);
                $this->rulesResource->save($rules);
            }

            // display success message
            $this->messageManager->addSuccessMessage(__('The form has been duplicated.'));
            return $resultRedirect->setPath('*/form/edit', [FormInterface::ID => $newForm->getId()]);
        } catch (Exception $e) {

            // display error message
            $this->messageManager->addErrorMessage($e->getMessage());

            // go back to edit form
            return $resultRedirect->setPath('*/*/edit', [FormInterface::ID => $id]);
        }

    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed(): bool
    {
        $isAllowed = parent::_isAllowed();
        $id        = (int)$this->getRequest()->getParam(FormInterface::ID);
        if ($id && !$this->accessHelper->isAllowed($id)) {
            $isAllowed = false;
        }
        return $isAllowed;
    }
}
