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

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Helper\Form\ImportHelper as ImportFormHelper;
use MageMe\WebForms\Model\FormFactory;
use Magento\Authorization\Model\ResourceModel\Rules as RulesResource;
use Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory;
use Magento\Authorization\Model\RulesFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\Authorization\RoleLocator;
use Magento\Framework\Acl\Builder;
use Magento\Framework\Exception\AlreadyExistsException;

class Import extends Action
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
     * @var CollectionFactory
     */
    protected $rulesCollectionFactory;

    /**
     * @var Builder
     */
    protected $aclBuilder;

    /**
     * @var Session
     */
    protected $authSession;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var ImportFormHelper
     */
    protected $importFormHelper;

    /**
     * Import constructor.
     * @param ImportFormHelper $importFormHelper
     * @param FormFactory $formFactory
     * @param Session $authSession
     * @param Builder $aclBuilder
     * @param CollectionFactory $rulesCollectionFactory
     * @param RulesFactory $rulesFactory
     * @param RulesResource $rulesResource
     * @param RoleLocator $roleLocator
     * @param Context $context
     */
    public function __construct(
        ImportFormHelper  $importFormHelper,
        FormFactory       $formFactory,
        Session           $authSession,
        Builder           $aclBuilder,
        CollectionFactory $rulesCollectionFactory,
        RulesFactory      $rulesFactory,
        RulesResource     $rulesResource,
        RoleLocator       $roleLocator,
        Context           $context)
    {
        parent::__construct($context);
        $this->roleLocator            = $roleLocator;
        $this->rulesResource          = $rulesResource;
        $this->rulesFactory           = $rulesFactory;
        $this->rulesCollectionFactory = $rulesCollectionFactory;
        $this->aclBuilder             = $aclBuilder;
        $this->authSession            = $authSession;
        $this->formFactory            = $formFactory;
        $this->importFormHelper       = $importFormHelper;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $file = $this->getRequest()->getFiles('import_form');
        if (!$file) {
            $this->messageManager->addErrorMessage(__('The uploaded file contains invalid data.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $importData = file_get_contents($file['tmp_name']);
        $form       = $this->importFormHelper->import($importData);
        if ($form && empty($this->importFormHelper->getErrors())) {
            if (!$this->_authorization->isAllowed('Magento_Backend::all')) {
                try {
                    $this->updateRolePermission($form);
                } catch (AlreadyExistsException $e) {
                    $this->messageManager->addErrorMessage(__('Error happened during update role permission: %1', $e->getMessage()));
                }
            }
            $this->messageManager->addSuccessMessage(__('Form "%1" successfully imported.', $form->getName()));
        }
        foreach ($this->importFormHelper->getErrors() as $error) {
            $this->messageManager->addErrorMessage($error);
        }
        foreach ($this->importFormHelper->getWarnings() as $warning) {
            $this->messageManager->addWarningMessage($warning);
        }
        return $this->_redirect('webforms/form/index');
    }

    /**
     * @param FormInterface $form
     * @throws AlreadyExistsException
     */
    protected function updateRolePermission(FormInterface $form)
    {
        $collection = $this->rulesCollectionFactory->create()
            ->addFilter('role_id', $this->roleLocator->getAclRoleId())
            ->addFilter('resource_id', 'MageMe_WebForms::form' . $form->getId())
            ->addFilter('permission', 'allow');
        if ($collection->count() === 0) {
            $rules = $this->rulesFactory->create()->setData([
                'role_id' => $this->roleLocator->getAclRoleId(),
                'resource_id' => 'MageMe_WebForms::form' . $form->getId(),
                'permission' => 'allow'
            ]);
            $this->rulesResource->save($rules);
        }
        $this->authSession->setAcl($this->aclBuilder->getAcl());
    }
}
