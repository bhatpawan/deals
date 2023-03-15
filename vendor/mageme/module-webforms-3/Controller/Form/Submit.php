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

namespace MageMe\WebForms\Controller\Form;

use Exception;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Block\Form as FormBlock;
use MageMe\WebForms\Controller\AbstractAction;
use MageMe\WebForms\Helper\Result\PostHelper;
use MageMe\WebForms\Model\Form;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\Template;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManager;

class Submit extends AbstractAction
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var Header
     */
    protected $header;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @var PostHelper
     */
    protected $resultPostHelper;

    /**
     * Submit constructor.
     * @param Context $context
     * @param PostHelper $resultPostHelper
     * @param FormRepositoryInterface $formRepository
     * @param Header $header
     * @param ResultFactory $resultFactory
     * @param SessionFactory $customerSessionFactory
     * @param UrlInterface $url
     * @param ManagerInterface $messageManager
     * @param StoreManager $storeManager
     * @param FilterProvider $filterProvider
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context                 $context,
        PostHelper              $resultPostHelper,
        FormRepositoryInterface $formRepository,
        Header                  $header,
        ResultFactory           $resultFactory,
        SessionFactory          $customerSessionFactory,
        UrlInterface            $url,
        ManagerInterface        $messageManager,
        StoreManager            $storeManager,
        FilterProvider          $filterProvider,
        PageFactory             $pageFactory
    )
    {
        parent::__construct($context, $pageFactory);
        $this->filterProvider         = $filterProvider;
        $this->storeManager           = $storeManager;
        $this->messageManager         = $messageManager;
        $this->url                    = $url;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->resultFactory          = $resultFactory;
        $this->header                 = $header;
        $this->formRepository         = $formRepository;
        $this->resultPostHelper       = $resultPostHelper;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function execute()
    {
        $formId = (int)$this->request->getParam(FormInterface::ID);
        $form   = $this->formRepository->getById($formId, $this->storeManager->getStore()->getId());
        $filter = $this->filterProvider->getPageFilter();

        // Ajax submit
        if ($this->request->isAjax()) {
            return $this->ajaxSubmit($form, $filter);
        }

        // regular submit
        if ($this->getRequest()->getParam('submitForm_' . $form->getId()) && $form->getIsActive()) {
            return $this->regularSubmit($form, $filter);
        }

        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultLayout->setStatusHeader(404, '1.1', 'Not Found');
        $resultLayout->setHeader('Status', '404 File not found');
        return $resultLayout;
    }

    /**
     * @param FormInterface|Form $form
     * @param Template $filter
     * @return Json|ResultInterface
     * @throws Exception
     */
    protected function ajaxSubmit(FormInterface $form, Template $filter)
    {
        $result = ["success" => false, "errors" => []];
        if ($this->request->getParam('submitForm_' . $form->getId()) && $form->getIsActive()) {
            $resultObject = $this->resultPostHelper->savePostResult($form);
            if ($resultObject) {
                $result["success"] = true;

                // apply custom variables
                $formObject = new DataObject;
                $formObject->setData($form->getData());
                $subject = $resultObject->getSubject();
                $filter->setVariables([
                    'webform_name' => $form->getName(),
                    'webform_result' => $resultObject->toHtml(),
                    'result' => $resultObject->getTemplateResultVar(),
                    'webform' => $formObject,
                    'webform_subject' => $subject
                ]);
                $result["success_text"] = "&nbsp;";
                if ($form->getSuccessText()) {
                    $result["success_text"] = $filter->filter($form->getSuccessText());
                }

                if ($form->getRedirectUrl()) {
                    $result["redirect_url"] = $this->getRedirectUrl($form->getId(), $form->getStoreId(), $resultObject->getId());
                }

                if ($form->getAfterSubmissionScript()) {
                    $result[FormInterface::AFTER_SUBMISSION_SCRIPT] = $filter->filter($form->getAfterSubmissionScript());
                }
            } else {
                $errors = $this->messageManager->getMessages(true)->getItems();
                foreach ($errors as $err) {
                    $result["errors"][] = $err->getText();
                }
                $html_errors = "";
                if (count($result["errors"]) > 1) {
                    foreach ($result["errors"] as $err) {
                        $html_errors .= '<p>' . $err . '</p>';
                    }
                    $result["errors"] = $html_errors;
                } else {
                    $result["errors"] = $result["errors"][0];
                }
            }
        }

        if (!$form->getIsActive()) {
            $result["errors"][] = __('Web-form is not active.');
        }

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }

    /**
     * @param FormInterface|Form $form
     * @param Template $filter
     * @return mixed
     * @throws Exception
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function regularSubmit(FormInterface $form, Template $filter)
    {
        $resultId = null;

        // validate
        $result = $this->resultPostHelper->savePostResult($form);
        if ($result) {
            $this->customerSession = $this->customerSessionFactory->create();
            $this->customerSession->setData(FormBlock::FORM_SUCCESS, $form->getId());
            $this->customerSession->setData('webform_result_' . $form->getId(), $result->getId());
            $resultId = $result->getId();
        }
        $url = $this->header->getHttpReferer();
        if ($form->getRedirectUrl()) {
            $url = $this->getRedirectUrl($form->getId(), $form->getStoreId(), $resultId);
        }
        return $this->response->setRedirect($filter->filter($url));
    }

    /**
     * @param int $formId
     * @param int|null $storeId
     * @param int|null $resultId
     * @return string
     */
    private function getRedirectUrl(int $formId, ?int $storeId, ?int $resultId): string
    {
        return $this->url->getUrl('webforms/form/redirect', [
            FormInterface::ID => $formId,
            \MageMe\WebForms\Api\Data\ResultInterface::ID => $resultId,
            \MageMe\WebForms\Api\Data\ResultInterface::STORE_ID => $storeId,
        ]);
    }
}
