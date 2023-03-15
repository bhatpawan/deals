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

namespace MageMe\WebForms\Block\Adminhtml;


use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Helper\StatisticsHelper;
use MageMe\WebForms\Model\Form;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;

/**
 *
 */
class ToolbarEntry extends Template
{
    const CONFIG_ADMIN_TOOLBAR = 'webforms/general/admin_toolbar';

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * @var FormInterface[]
     */
    protected $forms;
    /**
     * @var StatisticsHelper
     */
    private $statisticsHelper;

    /**
     * Menu constructor.
     * @param FormRepositoryInterface $formRepository
     * @param ResultRepositoryInterface $resultRepository
     * @param SortOrderBuilder $sortOrderBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StatisticsHelper $statisticsHelper
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        FormRepositoryInterface   $formRepository,
        ResultRepositoryInterface $resultRepository,
        SortOrderBuilder          $sortOrderBuilder,
        SearchCriteriaBuilder     $searchCriteriaBuilder,
        StatisticsHelper          $statisticsHelper,
        Context                   $context,
        array                     $data = []
    )
    {
        parent::__construct($context, $data);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder      = $sortOrderBuilder;
        $this->formRepository        = $formRepository;
        $this->resultRepository      = $resultRepository;
        $this->statisticsHelper      = $statisticsHelper;
    }

    public function _beforeToHtml(): ToolbarEntry
    {
        if (!$this->statisticsHelper->getUnreadCron()) $this->statisticsHelper->calculateUnreadCount();

        return parent::_beforeToHtml();
    }

    /**
     * @return bool
     */
    public function isSettingsAllowed(): bool
    {
        return $this->getAuthorization()->isAllowed('MageMe_WebForms::settings');
    }

    /**
     * @return bool
     */
    public function isQuickresponseAllowed(): bool
    {
        return $this->getAuthorization()->isAllowed('MageMe_WebForms::quickresponse');
    }

    /**
     * @return int
     */
    public function getTotalUnreadCount(): int
    {
        if (!$this->statisticsHelper->getUnreadEnable()) return 0;

        if ($this->isManageFormsAllowed()) {
            return $this->statisticsHelper->getTotalUnreadCount();
        }

        $unreadCount = 0;
        $forms       = $this->getForms();
        foreach ($forms as $form) {
            if ($this->isFormAllowed($form)) {
                $unreadCount += $form->getUnreadCount();
            }
        }
        return $unreadCount;
    }

    /**
     * @return bool
     */
    public function isManageFormsAllowed(): bool
    {
        return $this->getAuthorization()->isAllowed('MageMe_WebForms::manage_forms');
    }

    /**
     * @return FormInterface[]|Form[]
     */
    public function getForms(): array
    {
        // check available forms
        if (!$this->forms) {
            $sortOrder      = $this->sortOrderBuilder
                ->setField(FormInterface::NAME)
                ->setAscendingDirection()
                ->create();
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(FormInterface::IS_MENU_LINK_ENABLED, 1)
                ->addSortOrder($sortOrder)
                ->create();
            $this->forms    = $this->formRepository->getList($searchCriteria)->getItems();
        }
        return $this->forms;
    }

    /**
     * @param FormInterface $form
     * @return bool
     */
    public function isFormAllowed(FormInterface $form): bool
    {
        return $this->getAuthorization()->isAllowed('MageMe_WebForms::form' . $form->getId());
    }

    /**
     * @param FormInterface $form
     * @return int
     */
    public function getFormUnreadCount(FormInterface $form): int
    {
        if (!$this->statisticsHelper->getUnreadEnable()) return 0;

        return $form->getUnreadCount();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        return $this->_scopeConfig->getValue(self::CONFIG_ADMIN_TOOLBAR) ?
            parent::_toHtml() :
            '';
    }
}
