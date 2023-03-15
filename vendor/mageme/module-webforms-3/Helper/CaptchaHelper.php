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

namespace MageMe\WebForms\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Math\Random;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\HTTP\Client\Curl;


class CaptchaHelper
{
    const V2_PUBLIC_KEY = 'webforms/captcha/public_key';
    const V2_PRIVATE_KEY = 'webforms/captcha/private_key';
    const V3_PUBLIC_KEY = 'webforms/captcha/public_key3';
    const V3_PRIVATE_KEY = 'webforms/captcha/private_key3';
    const RECAPTCHA_VERSION = 'webforms/captcha/recaptcha_version';
    const POSITION = 'webforms/captcha/position';
    const THEME = 'webforms/captcha/theme';
    const SCORE_THRESHOLD = 'webforms/captcha/score_threshold';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $publicKey;

    /**
     * @var string
     */
    protected $privateKey;

    /**
     * @var float
     */
    protected $scoreThreshold;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var string
     */
    protected $position;

    /**
     * @var string
     */
    protected $theme;

    /** @var Random */
    protected $random;

    /** @var Registry */
    protected $registry;

    /** @var Resolver * */
    protected $localeResolver;

    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Registry $registry
     * @param Random $random
     * @param Resolver $localeResolver
     * @param RemoteAddress $remoteAddress
     * @param Curl $curl
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Registry             $registry,
        Random               $random,
        Resolver             $localeResolver,
        RemoteAddress        $remoteAddress,
        Curl                 $curl
    )
    {
        $this->random         = $random;
        $this->registry       = $registry;
        $this->localeResolver = $localeResolver;
        $this->scopeConfig    = $scopeConfig;
        $this->remoteAddress  = $remoteAddress;
        $this->curl           = $curl;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getId(): string
    {
        if (!$this->id) {
            $this->id = $this->random->getRandomString(6);
        }
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        if (!$this->publicKey) {
            $this->publicKey =
                (string)$this->scopeConfig->getValue(
                    $this->getVersion() == '3' ? self::V3_PUBLIC_KEY : self::V2_PUBLIC_KEY,
                    ScopeInterface::SCOPE_STORE);
        }
        return $this->publicKey;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setPublicKey(string $value): CaptchaHelper
    {
        $this->publicKey = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        if (!$this->version) {
            $this->version = (string)$this->scopeConfig->getValue(self::RECAPTCHA_VERSION, ScopeInterface::SCOPE_STORE);
        }
        return $this->version;
    }

    /**
     * @param string $version
     * @return $this
     */
    public function setVersion(string $version): CaptchaHelper
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        if (!$this->privateKey) {
            $this->privateKey =
                (string)$this->scopeConfig->getValue(
                    $this->getVersion() == '3' ? self::V3_PRIVATE_KEY : self::V2_PRIVATE_KEY,
                    ScopeInterface::SCOPE_STORE);
        }
        return $this->privateKey;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setPrivateKey(string $value): CaptchaHelper
    {
        $this->privateKey = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getPosition(): string
    {
        if (!$this->position) {
            $this->position = (string)$this->scopeConfig->getValue(self::POSITION, ScopeInterface::SCOPE_STORE);
        }
        if ($this->getVersion() == 2) return 'inline';
        return $this->position ?? 'inline';
    }

    /**
     * @param string $position
     * @return $this
     */
    public function setPosition(string $position): CaptchaHelper
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return string
     */
    public function getTheme(): string
    {
        if (!$this->theme) {
            $this->theme = (string)$this->scopeConfig->getValue(self::THEME, ScopeInterface::SCOPE_STORE);
        }
        return $this->theme ?? 'standard';
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setTheme(string $value): CaptchaHelper
    {
        $this->theme = $value;
        return $this;
    }

    /**
     * @param string $response
     * @return bool
     */
    public function verify(string $response): bool
    {
        //Get user ip
        $ip = $this->remoteAddress->getRemoteAddress();

        //Build up the url
        $url      = 'https://www.google.com/recaptcha/api/siteverify';
        $full_url = $url . '?secret=' . $this->privateKey . '&response=' . $response . '&remoteip=' . $ip;

        //Get the response back decode the json
        $data = json_decode($this->getCurlData($full_url));

        if (isset($data->score) && (float)$data->score <= $this->getScoreThreshold())
            return false;

        //Return true or false, based on users input
        if (isset($data->success) && $data->success == true) {
            return true;
        }

        return false;
    }

    /**
     * @param string $url
     * @return string
     */
    function getCurlData(string $url)
    {
        $this->curl->setOption(CURLOPT_URL, $url);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
        $this->curl->setOption(CURLOPT_TIMEOUT, 10);
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, 0);
        $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, 0);
        $this->curl->setOption(CURLOPT_USERAGENT,
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16");
        $this->curl->get($url);
        return $this->curl->getBody();
    }

    /**
     * @return float
     */
    public function getScoreThreshold(): float
    {
        if (!$this->scoreThreshold) {
            $this->scoreThreshold = (float)$this->scopeConfig->getValue(self::SCORE_THRESHOLD, ScopeInterface::SCOPE_STORE);
        }
        if ($this->getVersion() == 2) return 0.5;
        return $this->scoreThreshold ?? 0.5;
    }
}
