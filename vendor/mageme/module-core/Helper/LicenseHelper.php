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

namespace MageMe\Core\Helper;


use MageMe\Core\Api\LicenseHelperInterface;
use MageMe\Core\Api\Ui\LicenseInterface as uiLicenseInterface;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory as ConfigCollectionFactory;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;


/**
 *
 */
class LicenseHelper implements LicenseHelperInterface
{

    /**
     *
     */
    const URL = 'https://license.mageme.com';

    /**
     *
     */
    const ACTION_ACTIVATE = 'activate';

    /**
     *
     */
    const ACTION_DEACTIVATE = 'deactivate';

    /**
     *
     */
    const ACTION_VERIFY = 'verify';

    /**
     *
     */
    const PATH_LICENSE = '/license';

    /**
     *
     */
    const PATH_ACTIVE = self::PATH_LICENSE . '/active';

    /**
     *
     */
    const PATH_SERIAL = self::PATH_LICENSE . '/serial';

    /**
     *
     */
    const PATH_ACCESS_TOKEN = self::PATH_LICENSE . '/access_token';

    /**
     *
     */
    const PATH_VERIFIED_TIME = self::PATH_LICENSE . '/verified_time';

    /**
     *
     */
    const PATH_DEVELOPMENT = self::PATH_LICENSE . '/development';

    /**
     *
     */
    const PATH_VERIFY_ATTEMPT = self::PATH_LICENSE . '/verify_attempt';

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ReinitableConfigInterface
     */
    protected $reinitableConfig;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var uiLicenseInterface
     */
    protected $ui;
    /**
     * @var ConfigCollectionFactory
     */
    private $configCollectionFactory;


    /**
     * LicenseHelper constructor.
     * @param Curl $curl
     * @param Config $config
     * @param ScopeConfigInterface $scopeConfig
     * @param ReinitableConfigInterface $reinitableConfig
     * @param ModuleListInterface $moduleList
     * @param ProductMetadataInterface $productMetadata
     * @param DateTime $dateTime
     * @param uiLicenseInterface $ui
     * @param ConfigCollectionFactory $configCollectionFactory
     */
    public function __construct(
        Curl                      $curl,
        Config                    $config,
        ScopeConfigInterface      $scopeConfig,
        ReinitableConfigInterface $reinitableConfig,
        ModuleListInterface       $moduleList,
        ProductMetadataInterface  $productMetadata,
        DateTime                  $dateTime,
        uiLicenseInterface        $ui,
        ConfigCollectionFactory   $configCollectionFactory
    )
    {
        $this->moduleList              = $moduleList;
        $this->curl                    = $curl;
        $this->scopeConfig             = $scopeConfig;
        $this->config                  = $config;
        $this->reinitableConfig        = $reinitableConfig;
        $this->productMetadata         = $productMetadata;
        $this->dateTime                = $dateTime;
        $this->ui                      = $ui;
        $this->configCollectionFactory = $configCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function getModuleTitle()
    {
        return __('Core');
    }

    /**
     * @return uiLicenseInterface
     */
    public function getUi(): uiLicenseInterface
    {
        return $this->ui;
    }

    /**
     * @param $serial
     * @return array
     */
    public function activateLicense($serial): array
    {
        $params = $this->getRequestParams(self::ACTION_ACTIVATE, $serial);
        $result = $this->sendRequest($params);

        $this->saveLicenseConfig(self::PATH_SERIAL, $serial);

        if ($result['success']) {
            $result['messages'][] = __('License successfully activated.');
            $result['is_active']  = true;
            $this->saveLicenseConfig(self::PATH_ACTIVE, 1);
            $this->saveLicenseConfig(self::PATH_ACCESS_TOKEN, $result['access_token']);
            $this->saveLicenseConfig(self::PATH_VERIFIED_TIME, $this->dateTime->gmtTimestamp());
            $this->saveLicenseConfig(self::PATH_VERIFY_ATTEMPT, 0);
            if ($result['dev']) {
                $this->saveLicenseConfig(self::PATH_DEVELOPMENT, 1);
                $result['warnings'][] = __('Development license detected. Please do not use for production.');
            }
        } else {
            $this->saveLicenseConfig(self::PATH_ACTIVE, 0);
        }

        $this->reinitableConfig->reinit();
        unset($result['access_token']);
        return $result;
    }

    /**
     * @param string $action
     * @param null $serial
     * @return array
     */
    private function getRequestParams(string $action, $serial = null): array
    {
        if (!$serial) $serial = $this->getSerial();

        $moduleInfo = $this->moduleList->getOne($this->getModuleName());

        return [
            'action' => $action,
            'serial' => $serial,
            'platform' => $this->getPlatformCode(),
            'magento_edition' => $this->productMetadata->getEdition(),
            'magento_version' => $this->productMetadata->getVersion(),
            'module_name' => $this->getModuleName(),
            'module_version' => (string)$moduleInfo['setup_version'],
            'access_token' => (string)$this->getLicenseConfig(self::PATH_ACCESS_TOKEN),
            'activated_url' => (string)$this->scopeConfig->getValue('web/unsecure/base_url')
        ];
    }

    /**
     * @return null|string
     */
    public function getSerial(): ?string
    {
        return $this->getLicenseConfig(self::PATH_SERIAL);
    }

    /**
     * @param $pathId
     * @return mixed
     */
    protected function getLicenseConfig($pathId)
    {
        $collection = $this->configCollectionFactory->create();
        $collection->addFieldToFilter('path', ['eq' => $this->getConfigSection() . $pathId]);
        return $collection->count() ? $collection->getFirstItem()->getData()['value'] : '';
    }

    /**
     * @inheritdoc
     */
    public function getConfigSection(): string
    {
        return 'core';
    }

    /**
     * @inheritdoc
     */
    public function getModuleName(): string
    {
        return 'MageMe_Core';
    }

    /**
     * @return string
     */
    protected function getPlatformCode(): string
    {
        $version = 'M2';
        $this->productMetadata->getEdition() == 'Community' ?
            $edition = 'CE' :
            $edition = 'EE';
        return $version . $edition;
    }

    /**
     * @param $params
     * @return array
     */
    private function sendRequest($params): array
    {
        $result = [
            'success' => false,
            'is_active' => false,
            'messages' => [],
            'warnings' => [],
            'errors' => [],
        ];

        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->curl->setOption(CURLOPT_USERAGENT,
            'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16');
        $this->curl->get($this->getLicenseUrl($params));
        $response = json_decode($this->curl->getBody(), true);

        if (!$response) {
            $result['errors'][] = __('Unexpected license server response.');
        } else {
            $result = array_merge($result, $response);
        }

        return $result;
    }

    /**
     * @param array $params
     * @return string
     */
    protected function getLicenseUrl(array $params): string
    {
        return static::URL . '?' . http_build_query($params);
    }

    /**
     * @param $pathId
     * @param null $value
     * @return $this
     */
    protected function saveLicenseConfig($pathId, $value = null): LicenseHelper
    {
        return $this->saveConfig($this->getConfigSection() . $pathId, $value);
    }

    /**
     * @param $pathId
     * @param null $value
     * @return $this
     */
    protected function saveConfig($pathId, $value = null): LicenseHelper
    {
        if (is_array($pathId)) {
            foreach ($pathId as $path => $pathValue) {
                $this->saveConfig($path, $pathValue);
            }

            return $this;
        }

        $this->config->saveConfig(
            $pathId,
            $value,
            'default'
        );

        return $this;
    }

    /**
     * @return array
     */
    public function deactivateLicense(): array
    {
        $params = $this->getRequestParams(self::ACTION_DEACTIVATE);
        $result = $this->sendRequest($params);

        $this->saveLicenseConfig(self::PATH_ACTIVE, 0);
        $this->saveLicenseConfig(self::PATH_DEVELOPMENT, 0);
        $this->saveLicenseConfig(self::PATH_ACCESS_TOKEN, '');

        if ($result['success']) {
            $result['messages'][] = __('License successfully deactivated!');
            $this->saveLicenseConfig(self::PATH_ACTIVE, 0);
            $this->saveLicenseConfig(self::PATH_DEVELOPMENT, 0);
            $this->saveLicenseConfig(self::PATH_ACCESS_TOKEN, $result['access_token']);
        } else {
            $result['is_active'] = false;
        }
        $this->reinitableConfig->reinit();

        unset($result['access_token']);
        return $result;
    }

    /**
     * @return void
     */
    public function verifyLicense()
    {
        $lastVerifiedTime = (int)$this->getLicenseConfig(self::PATH_VERIFIED_TIME);
        $currentTime      = $this->dateTime->gmtTimestamp();

        if (($currentTime - $lastVerifiedTime) > 86400 * 21
            && $this->isActive()) {
            $params = $this->getRequestParams(self::ACTION_VERIFY);
            $result = $this->sendRequest($params);

            if ($result['success']) {
                $this->saveLicenseConfig(self::PATH_VERIFIED_TIME, $currentTime);
                $this->saveLicenseConfig(self::PATH_ACCESS_TOKEN, $result['access_token']);
                $this->saveLicenseConfig(self::PATH_DEVELOPMENT, $result['dev']);
                $this->saveLicenseConfig(self::PATH_ACTIVE, $result['active']);
                $this->saveLicenseConfig(self::PATH_VERIFY_ATTEMPT, 0);
            } else {
                $verifyAttempt = (int)$this->getLicenseConfig(self::PATH_VERIFY_ATTEMPT) + 1;
                $this->saveLicenseConfig(self::PATH_VERIFY_ATTEMPT, $verifyAttempt);
                if ($verifyAttempt > 5)
                    $this->saveLicenseConfig(self::PATH_ACTIVE, 0);
            }

            $this->reinitableConfig->reinit();
        }
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->getLicenseConfig(self::PATH_ACTIVE) && $this->getSerial();
    }
}
