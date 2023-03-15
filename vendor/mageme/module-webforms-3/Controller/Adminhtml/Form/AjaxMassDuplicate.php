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


use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Model\ResourceModel\Form\CollectionFactory;
use Magento\Authorization\Model\ResourceModel\Rules as RulesResource;
use Magento\Authorization\Model\RulesFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Authorization\RoleLocator;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Ui\Component\MassAction\Filter;

class AjaxMassDuplicate extends AbstractAjaxFormMassAction
{
    /**
     * @inheritdoc
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::add_form';
    const ACTION = 'duplicate';

    /**
     * @var RoleLocator
     */
    private $roleLocator;
    /**
     * @var RulesResource
     */
    private $rulesResource;
    /**
     * @var RulesFactory
     */
    private $rulesFactory;

    /**
     * @param RulesFactory $rulesFactory
     * @param RulesResource $rulesResource
     * @param RoleLocator $roleLocator
     * @param FormRepositoryInterface $repository
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     * @param JsonFactory $jsonFactory
     * @param Context $context
     */
    public function __construct(
        RulesFactory            $rulesFactory,
        RulesResource           $rulesResource,
        RoleLocator             $roleLocator,
        FormRepositoryInterface $repository,
        CollectionFactory $collectionFactory,
        Filter $filter,
        JsonFactory $jsonFactory,
        Context $context
    ) {
        parent::__construct($repository, $collectionFactory, $filter, $jsonFactory, $context);
        $this->roleLocator    = $roleLocator;
        $this->rulesResource  = $rulesResource;
        $this->rulesFactory   = $rulesFactory;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    protected function action(AbstractDb $collection): Phrase
    {
        foreach ($collection as $item) {
            $model = $this->repository->getById($item->getId());
            $newForm = $model->duplicate();

            // update role permissions
            if (!$this->_authorization->isAllowed('Magento_Backend::all')) {
                $rules = $this->rulesFactory->create()->setData([
                    'role_id' => $this->roleLocator->getAclRoleId(),
                    'resource_id' => 'MageMe_WebForms::form' . $newForm->getId(),
                    'permission' => 'allow'
                ]);
                $this->rulesResource->save($rules);
            }
        }
        return __('A total of %1 record(s) have been duplicated.', $collection->getSize());
    }
}
