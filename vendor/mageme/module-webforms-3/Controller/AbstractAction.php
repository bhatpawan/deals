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

namespace MageMe\WebForms\Controller;


use Magento\Framework\App\Action\AbstractAction as DeprecatedAbstractAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Profiler;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class AbstractAction
 * Extends DeprecatedAbstractAction for compatibility with Magento 2.3
 * @TODO update when 2.3 support is no longer needed
 * @package MageMe\WebForms\Controller
 */
abstract class AbstractAction extends DeprecatedAbstractAction
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var ActionFlag
     */
    protected $actionFlag;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * AbstractAction constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context     $context,
        PageFactory $pageFactory
    )
    {
        parent::__construct($context);
        $this->request     = $context->getRequest();
        $this->response    = $context->getResponse();
        $this->redirect    = $context->getRedirect();
        $this->actionFlag  = $context->getActionFlag();
        $this->pageFactory = $pageFactory;
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        $this->request = $request;
        $profilerKey   = 'CONTROLLER_ACTION:' . $request->getFullActionName();
        Profiler::start($profilerKey);

        $result = null;
        if ($request->isDispatched() && !$this->actionFlag->get('', self::FLAG_NO_DISPATCH)) {
            Profiler::start('action_body');
            $result = $this->execute();
            Profiler::stop('action_body');
        }
        Profiler::stop($profilerKey);
        return $result ?: $this->response;
    }

    /**
     * Retrieve request object
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Retrieve response object
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Returns ActionFlag value
     *
     * @return ActionFlag
     */
    public function getActionFlag(): ActionFlag
    {
        return $this->actionFlag;
    }

    /**
     * Throw control to different action (control and module if was specified).
     *
     * @param string $action
     * @param string|null $controller
     * @param string|null $module
     * @param array|null $params
     * @return void
     */
    protected function forward(string $action, string $controller = null, string $module = null, array $params = null)
    {
        $request = $this->request;

        $request->initForward();

        if (isset($params)) {
            $request->setParams($params);
        }

        if (isset($controller)) {
            $request->setControllerName($controller);

            // Module should only be reset if controller has been specified
            if (isset($module)) {
                $request->setModuleName($module);
            }
        }

        $request->setActionName($action);
        $request->setDispatched(false);
    }

    /**
     * Set redirect into response
     *
     * @param string $path
     * @param array $arguments
     * @return ResponseInterface
     */
    protected function redirect(string $path, array $arguments = []): ResponseInterface
    {
        $this->redirect->redirect($this->response, $path, $arguments);
        return $this->response;
    }
}