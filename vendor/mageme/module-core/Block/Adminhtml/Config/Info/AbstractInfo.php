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

namespace MageMe\Core\Block\Adminhtml\Config\Info;


use Exception;
use MageMe\Core\Plugin\Magento\Config\Model\Config\Structure\Data\ConfigMerge;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\ModuleListInterface;

abstract class AbstractInfo extends Field
{
    /** Json fields */
    const DESCRIPTION = 'description';
    const IMAGE = 'image';
    const NAME = 'name';
    const PRICE = 'price';
    const RELEASE_NOTES = 'release_notes';
    const URL = 'url';
    const VERSION = 'version';
    const LINKS = 'links';
    const ADDONS = 'add-ons';

    /**
     * Cache group Tag
     */
    const CACHE_GROUP = Config::TYPE_IDENTIFIER;

    /**
     * Prefix for cache key of block
     */
    const CACHE_KEY_PREFIX = 'MAGEME_';

    /**
     * Cache tag
     */
    const CACHE_TAG = 'extensions';

    /**
     * Mageme api url to get extension json
     */
    const API_URL = 'https://info.mageme.com/modules.json';

    /**
     * @var ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var ProductMetadataInterface
     */
    protected $_metadata;

    public function __construct(
        ModuleListInterface      $moduleList,
        Context                  $context,
        ProductMetadataInterface $metadata,
        array                    $data = []
    )
    {
        $this->_moduleList = $moduleList;
        $this->_metadata   = $metadata;
        parent::__construct($context, $data);
    }

    /**
     * @param string $moduleName
     * @return array
     */
    public function getModuleInfo(string $moduleName)
    {
        $info = $this->getInfo();
        if (isset($info[$moduleName])) {
            return $info[$moduleName];
        }
        return [];
    }

    /**
     * @return bool|mixed|string
     */
    public function getInfo()
    {
        $result = $this->_loadCache();
        if (!$result) {
            try {
                $result = file_get_contents(self::API_URL);
                $this->_saveCache($result);
            } catch (Exception $e) {
                return false;
            }
        }

        return json_decode($result, true);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function getElementModuleName(AbstractElement $element): string
    {
        $config = $element->getData('field_config');
        return $config[ConfigMerge::MODULE_NAME] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function render(AbstractElement $element): string
    {
        return empty($this->_getElementHtml($element)) ? '' : parent::render($element);
    }
}
