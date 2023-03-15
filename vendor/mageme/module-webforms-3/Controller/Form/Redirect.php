<?php

namespace MageMe\WebForms\Controller\Form;

use Exception;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Controller\AbstractAction;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\Factory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;

class Redirect extends AbstractAction
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var Factory
     */
    protected $messageFactory;
    /**
     * @var UrlInterface
     */
    private $url;
    /**
     * @var FormRepositoryInterface
     */
    private $formRepository;
    /**
     * @var FilterProvider
     */
    private $filterProvider;
    /**
     * @var ResultRepositoryInterface
     */
    private $resultRepository;

    /**
     * @param ResultRepositoryInterface $resultRepository
     * @param FilterProvider $filterProvider
     * @param FormRepositoryInterface $formRepository
     * @param UrlInterface $url
     * @param Factory $messageFactory
     * @param ManagerInterface $messageManager
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        ResultRepositoryInterface $resultRepository,
        FilterProvider          $filterProvider,
        FormRepositoryInterface $formRepository,
        UrlInterface            $url,
        Factory                 $messageFactory,
        ManagerInterface        $messageManager,
        Context                 $context,
        PageFactory             $pageFactory
    ) {
        parent::__construct($context, $pageFactory);
        $this->messageManager = $messageManager;
        $this->messageFactory = $messageFactory;
        $this->url            = $url;
        $this->formRepository = $formRepository;
        $this->filterProvider = $filterProvider;
        $this->resultRepository = $resultRepository;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function execute()
    {
        $formId  = (int)$this->request->getParam(FormInterface::ID);
        $storeId = (int)$this->request->getParam(ResultInterface::STORE_ID);
        $resultId = (int)$this->request->getParam(ResultInterface::ID);
        $form    = $this->formRepository->getById($formId, $storeId);
        $filter  = $this->filterProvider->getPageFilter();

        $filter->setVariables([
            'webform_name' => $form->getName(),
            'webform' => new DataObject($form->getData()),
        ]);
        if ($resultId) {
            $result = $this->resultRepository->getById($resultId);
            $filter->setVariables([
                'webform_result' => $result->toHtml(),
                'result' => $result->getTemplateResultVar(),
                'webform_subject' => $result->getSubject(),
            ]);
        }

        if (!$form->getRedirectUrl()) {
            $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $resultLayout->setStatusHeader(404, '1.1', 'Not Found');
            $resultLayout->setHeader('Status', '404 File not found');
            return $resultLayout;
        }

        if (strstr((string)$form->getRedirectUrl(), '://')) {
            $url = $form->getRedirectUrl();
        } else {
            $url = $this->url->getUrl($form->getRedirectUrl());
        }
        if ($form->getIsSuccessSessionDisplayed()) {
            $this->messageManager->addMessage($this->messageFactory->create(MessageInterface::TYPE_SUCCESS,
                $filter->filter($form->getSuccessText())));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($filter->filter($url));
        return $resultRedirect;
    }
}