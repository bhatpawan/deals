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

namespace MageMe\WebForms\Block\Customer\Account;

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Config\Options\Result\ApprovalStatus;
use MageMe\WebForms\Config\Options\Result\Permission;
use MageMe\WebForms\Model\Form;
use MageMe\WebForms\Model\ResourceModel;
use MageMe\WebForms\Model\ResourceModel\Result\CollectionFactory as ResultCollectionFactory;
use MageMe\WebForms\Model\Result;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Pager;


/**
 * Class Results
 * @package MageMe\WebForms\Block\Customer\Account
 */
class Results extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var  ResourceModel\Result\Collection
     */
    protected $resultCollection;

    /**
     * @var Pager
     */
    protected $htmlPagerBlock;

    /**
     * @var Session
     */
    protected $_session;

    /**
     * @var ApprovalStatus
     */
    protected $approvalStatus;

    /**
     * @var ResultCollectionFactory
     */
    private $resultCollectionFactory;

    /**
     * Results constructor.
     * @param ApprovalStatus $approvalStatus
     * @param Template\Context $context
     * @param ResultCollectionFactory $resultCollectionFactory
     * @param Registry $coreRegistry
     * @param Pager $htmlPagerBlock
     * @param SessionFactory $sessionFactory
     * @param array $data
     */
    public function __construct(
        ApprovalStatus          $approvalStatus,
        Template\Context        $context,
        ResultCollectionFactory $resultCollectionFactory,
        Registry                $coreRegistry,
        Pager                   $htmlPagerBlock,
        SessionFactory          $sessionFactory,
        array                   $data = []
    )
    {
        parent::__construct($context, $data);
        $this->resultCollectionFactory = $resultCollectionFactory;
        $this->registry                = $coreRegistry;
        $this->htmlPagerBlock          = $htmlPagerBlock;
        $this->_session                = $sessionFactory->create();
        $this->approvalStatus          = $approvalStatus;
    }

    /**
     * @return $this|Template
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($toolbar = $this->htmlPagerBlock) {
            $toolbar->setCollection($this->getCollection());
            $this->addChild('toolbar', $toolbar);
        }

        return $this;
    }

    /**
     * @return ResourceModel\Result\Collection
     */
    public function getCollection(): ResourceModel\Result\Collection
    {
        if (null === $this->resultCollection) {
            $webform                = $this->getForm();
            $this->resultCollection = $this->resultCollectionFactory->create()
                ->setLoadValues(true)
                ->addFilter(ResultInterface::FORM_ID, $webform->getId())
                ->addFilter(ResultInterface::CUSTOMER_ID, $this->registry->registry(ResultInterface::CUSTOMER_ID))
                ->addOrder(ResultInterface::CREATED_AT, 'desc');
        }
        return $this->resultCollection;
    }

    /**
     * @param Result $result
     * @return array
     */
    public function getActions(Result $result): array
    {
        $actions = [];
        if ($this->getPermission(Permission::VIEW)) {
            $actions[] = [
                'href' => $this->getUrlResultView($result),
                'label' => __('View'),
                'sortOrder' => 10,
                'class' => 'result-action-view',
            ];
        }
        if ($this->getPermissionByResult(Permission::EDIT, $result)) {
            $actions[] = [
                'href' => $this->getUrlResultEdit($result),
                'label' => __('Edit'),
                'sortOrder' => 20,
                'class' => 'result-action-edit',
            ];
        }
        if ($this->getPermissionByResult(Permission::DELETE, $result)) {
            $actions[] = [
                'href' => $this->getUrlResultDelete($result),
                'label' => __('Delete'),
                'sortOrder' => 30,
                'class' => 'result-action-delete',
                'onclick' => sprintf('return confirm(\'%s\');', __('Are you sure?'))
            ];
        }
        return $actions;
    }

    /**
     * @param string $permission
     * @return bool
     */
    public function getPermission(string $permission): bool
    {
        return in_array($permission, $this->getPermissions());
    }

    /**
     * @param string $permission
     * @param ResultInterface $result
     * @return bool
     */
    public function getPermissionByResult(string $permission, ResultInterface $result): bool
    {
        return in_array($permission, $this->getForm()->getCustomerResultPermissionsByResult($result));
    }

    /**
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->getForm()->getCustomerResultPermissions();
    }

    /**
     * @return FormInterface|Form|null
     */
    public function getForm()
    {
        return $this->registry->registry('webforms_form');
    }

    /**
     * @param Result $result
     * @return string
     */
    public function getUrlResultView(Result $result): string
    {
        return $this->getUrl('webforms/customer/result', [ResultInterface::ID => $result->getId()]);
    }

    /**
     * @param Result $result
     * @return string
     */
    public function getUrlResultEdit(Result $result): string
    {
        return $this->getUrl('webforms/result/edit', [ResultInterface::ID => $result->getId()]);
    }

    /**
     * @param Result $result
     * @return string
     */
    public function getUrlResultDelete(Result $result): string
    {
        return $this->getUrl('webforms/result/delete', [ResultInterface::ID => $result->getId()]);
    }
}
