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

namespace MageMe\WebForms\Ui\Component\Common\Listing\MassAction;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\AbstractComponent;

abstract class AbstractMassAction extends AbstractComponent
{
    const NAME = 'massaction';

    /**
     * Structure: [
     *     {actionType} => {aclName},
     *     {actionType} => {aclName},
     *     ...
     *     {actionType} => {aclName}
     * ];
     *
     * @var array
     */
    protected $actionsAcl = [];

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @param AuthorizationInterface $authorization
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     */
    public function __construct(
        AuthorizationInterface $authorization,
        ContextInterface       $context,
        array                  $components = [],
        array                  $data = [])
    {
        parent::__construct($context, $components, $data);
        $this->authorization = $authorization;
    }

    /**
     * @inheritdoc
     */
    public function getComponentName(): string
    {
        return static::NAME;
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $config = $this->getConfiguration();

        foreach ($this->getChildComponents() as $actionComponent) {
            $componentConfig = $actionComponent->getConfiguration();
            $disabledAction  = $componentConfig['actionDisable'] ?? false;
            if ($disabledAction) {
                continue;
            }
            if (!$this->isActionAllowed($componentConfig['type'] ?? '')) {
                continue;
            }
            $config['actions'][] = $componentConfig;
        }

        $origConfig = $this->getConfiguration();
        if ($origConfig !== $config) {
            $config = array_replace_recursive($config, $origConfig);
        }

        $this->setData('config', $config);
        $this->components = [];

        parent::prepare();
    }

    /**
     * Check if the given type of action is allowed
     *
     * @param string $actionType
     * @return bool
     */
    public function isActionAllowed(string $actionType): bool
    {
        foreach ($this->actionsAcl as $key => $acl) {
            if ($actionType == $key) {
                return $this->authorization->isAllowed($acl);
            }
        }
        return true;
    }
}