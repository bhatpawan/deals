<?php

namespace MageMe\WebForms\Model\Field\Type;

use MageMe\WebForms\Model\Field\AbstractField;
use Magento\Store\Model\ScopeInterface;

class GoogleMap extends AbstractField
{
    const ADDRESS = 'address';
    const LAT = 'lat';
    const LNG = 'lng';
    const ZOOM = 'zoom';

    #region type attributes
    /**
     * Get address
     *
     * @return string
     */
    public function getAddress(): string
    {
        return (string)$this->getData(self::ADDRESS);
    }

    /**
     * Set address
     *
     * @param string $address
     * @return $this
     */
    public function setAddress(string $address): GoogleMap
    {
        return $this->setData(self::ADDRESS, $address);
    }

    /**
     * Get latitude
     *
     * @return float
     */
    public function getLat(): float
    {
        return (float)$this->getData(self::LAT);
    }

    /**
     * Set latitude
     *
     * @param float $lat
     * @return $this
     */
    public function setLat(float $lat): GoogleMap
    {
        return $this->setData(self::LAT, $lat);
    }

    /**
     * Get longitude
     *
     * @return float
     */
    public function getLng(): float
    {
        return (float)$this->getData(self::LNG);
    }

    /**
     * Set longitude
     *
     * @param float $lng
     * @return $this
     */
    public function setLng(float $lng): GoogleMap
    {
        return $this->setData(self::LNG, $lng);
    }

    /**
     * Get zoom
     *
     * @return int
     */
    public function getZoom(): int
    {
        return (int)$this->getData(self::ZOOM);
    }

    /**
     * Set zoom
     *
     * @param int $zoom
     * @return $this
     */
    public function setZoom(int $zoom): GoogleMap
    {
        return $this->setData(self::ZOOM, $zoom);
    }
    #endregion

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return (string)$this->scopeConfig->getValue('webforms/map/google_api_key',
            ScopeInterface::SCOPE_STORE);
    }
}