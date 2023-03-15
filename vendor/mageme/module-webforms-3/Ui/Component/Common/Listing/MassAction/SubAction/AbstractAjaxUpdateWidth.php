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

namespace MageMe\WebForms\Ui\Component\Common\Listing\MassAction\SubAction;


use JsonSerializable;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Action;

abstract class AbstractAjaxUpdateWidth extends Action
{
    /**
     * Action controller URL
     * @var string
     */
    protected $controllerUrl = '';
    /**
     * @var string
     */
    protected $field = '';
    /**
     * @var string
     */
    protected $showedFlag = '';
    /**
     * @var string
     */
    protected $confirmTitle = 'Change width proportion';
    /**
     * @var array
     */
    protected $options;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * AbstractAjaxUpdateWidth constructor.
     * @param FormRepositoryInterface $formRepository
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     * @param array|JsonSerializable|null $actions
     */
    public function __construct(
        FormRepositoryInterface $formRepository,
        RequestInterface        $request,
        UrlInterface            $urlBuilder,
        ContextInterface        $context,
        array                   $components = [],
        array                   $data = [],
                                $actions = null
    )
    {
        parent::__construct($context, $components, $data, $actions);
        $this->urlBuilder     = $urlBuilder;
        $this->request        = $request;
        $this->formRepository = $formRepository;
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        $this->disable();
        $actions = [];
        if ($this->options) {
            foreach ($this->options as $option) {
                $label     = $option['label'];
                $value     = $option['value'];
                $actions[] = [
                    'type' => $label,
                    'label' => __($label),
                    'url' => $this->urlBuilder->getUrl(
                        $this->controllerUrl,
                        [$this->field => $value]
                    ),
                    'isAjax' => true,
                    'isSourceReloaded' => true,
                    'confirm' => [
                        'title' => __($this->confirmTitle),
                        'message' => __('Are you sure?'),
                        '__disableTmpl' => true,
                    ],
                ];
            }
        }

        $this->actions = $actions;
        parent::prepare();
    }

    /**
     * Disable action by special field
     */
    protected function disable()
    {
        $formId = (int)$this->request->getParam(FormInterface::ID);
        if (!$this->showedFlag || !$formId) {
            return;
        }
        try {
            $form                    = $this->formRepository->getById($formId);
            $config                  = $this->getConfiguration();
            $config['actionDisable'] = !$form->getData($this->showedFlag);
            $this->setData('config', $config);
        } catch (NoSuchEntityException $e) {
            return;
        }
    }
}