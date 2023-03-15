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

namespace MageMe\WebForms\Ui\Field\Type;


use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\Ui\FieldResultFormInterface;
use MageMe\WebForms\Api\Ui\FieldResultListingColumnInterface;
use MageMe\WebForms\Helper\HtmlHelper;
use MageMe\WebForms\Ui\Component\Result\Listing\Column\Field;
use MageMe\WebForms\Ui\Field\AbstractField;
use Magento\Cms\Model\Wysiwyg\Config;

class Wysiwyg extends AbstractField implements FieldResultListingColumnInterface, FieldResultFormInterface
{
    /**
     * @var Config
     */
    protected $wysiwygConfig;
    /**
     * @var HtmlHelper
     */
    protected $htmlHelper;

    public function __construct(
        HtmlHelper $htmlHelper,
        Config     $wysiwygConfig
    )
    {
        $this->wysiwygConfig = $wysiwygConfig;
        $this->htmlHelper    = $htmlHelper;
    }

    /**
     * @inheritDoc
     */
    public function getResultListingColumnConfig(int $sortOrder): array
    {
        $config                  = $this->getDefaultUIResultColumnConfig($sortOrder);
        $config['component']     = 'MageMe_WebForms/js/grid/columns/html';
        $config['bodyTmpl']      = 'MageMe_WebForms/grid/columns/textarea';
        $config['disableAction'] = true;
        return $config;
    }

    /**
     * @inheritDoc
     */
    public function getResultAdminFormConfig(ResultInterface $result = null): array
    {
        $config           = $this->getDefaultResultAdminFormConfig();
        $config['type']   = 'editor';
        $config['config'] = $this->wysiwygConfig->getConfig();
        return $config;
    }
}
