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

namespace MageMe\WebForms\Ui\Component\Common\Listing;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\Data\BookmarkInterface;
use Magento\Ui\Component\AbstractComponent;

abstract class AbstractUniqBookmark extends AbstractComponent
{
    const NAME = 'bookmark';

    /**
     * @var BookmarkManagementInterface
     */
    protected $bookmarkManagement;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param ContextInterface $context
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param RequestInterface $request
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface            $context,
        BookmarkManagementInterface $bookmarkManagement,
        RequestInterface            $request,
        array                       $components = [],
        array                       $data = []
    )
    {
        $this->request                                = $request;
        $data['config']['storageConfig']['namespace'] = $this->getNamespace();
        parent::__construct($context, $components, $data);
        $this->bookmarkManagement = $bookmarkManagement;
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $config    = [];
        $bookmarks = $this->bookmarkManagement->loadByNamespace($this->getNamespace());

        /** @var BookmarkInterface $bookmark */
        /** @noinspection PhpUndefinedMethodInspection */
        foreach ($bookmarks->getItems() as $bookmark) {
            if ($bookmark->isCurrent()) {
                $config['activeIndex'] = $bookmark->getIdentifier();
            }

            $config = array_merge_recursive($config, $bookmark->getConfig());
        }

        $this->setData('config', array_replace_recursive($config, $this->getConfiguration()));

        parent::prepare();

        $jsConfig = $this->getConfiguration();
        $this->getContext()->addComponentDefinition($this->getComponentName(), $jsConfig);
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName(): string
    {
        return static::NAME;
    }

    /**
     * @return string
     */
    abstract protected function getNamespace(): string;
}
