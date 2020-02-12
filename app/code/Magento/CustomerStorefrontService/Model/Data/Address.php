<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\Data;

use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\RegionInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Customer Address DTO class
 */
class Address extends AbstractSimpleObject implements AddressInterface
{
    /**
     * Get id
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->_get(self::ID);
    }

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getCustomerId(): ?int
    {
        return $this->_get(self::CUSTOMER_ID);
    }

    /**
     * Get first name
     *
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        return $this->_get(self::FIRSTNAME);
    }

    /**
     * Get last name
     *
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->_get(self::LASTNAME);
    }

    /**
     * Get region
     *
     * @return RegionInterface|null
     */
    public function getRegion(): ?RegionInterface
    {
        return $this->_get(self::REGION);
    }

    /**
     * Get region
     *
     * @return string|null
     */
    public function getRegionCode(): ?string
    {
        return $this->_get(self::REGION_CODE);
    }

    /**
     * Get country code
     *
     * @return string|null
     */
    public function getCountryCode(): ?string
    {
        return $this->_get(self::COUNTRY_CODE);
    }

    /**
     * Get street
     *
     * @return string[]|null
     */
    public function getStreet(): ?array
    {
        return $this->_get(self::STREET);
    }

    /**
     * Get telephone number
     *
     * @return string|null
     */
    public function getTelephone(): ?string
    {
        return $this->_get(self::TELEPHONE);
    }

    /**
     * Get fax number
     *
     * @return string|null
     */
    public function getFax(): ?string
    {
        return $this->_get(self::FAX);
    }

    /**
     * Get postcode
     *
     * @return string|null
     */
    public function getPostcode(): ?string
    {
        return $this->_get(self::POSTCODE);
    }

    /**
     * Get city name
     *
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->_get(self::CITY);
    }

    /**
     * Get if this address is default shipping address.
     *
     * @return bool
     */
    public function isDefaultShipping(): bool
    {
        return $this->_get(self::DEFAULT_SHIPPING);
    }

    /**
     * Get if this address is default billing address
     *
     * @return bool
     */
    public function isDefaultBilling(): bool
    {
        return $this->_get(self::DEFAULT_BILLING);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId(int $id): AddressInterface
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Set customer ID
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId(int $customerId): AddressInterface
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Set first name
     *
     * @param string $firstName
     * @return $this
     */
    public function setFirstname(string $firstName): AddressInterface
    {
        return $this->setData(self::FIRSTNAME, $firstName);
    }

    /**
     * Set last name
     *
     * @param string $lastName
     * @return $this
     */
    public function setLastname(string $lastName): AddressInterface
    {
        return $this->setData(self::LASTNAME, $lastName);
    }

    /**
     * Set region
     *
     * @param RegionInterface $region
     * @return $this
     */
    public function setRegion(RegionInterface $region): AddressInterface
    {
        return $this->setData(self::REGION, $region);
    }

    /**
     * Set Region Code
     *
     * @param string $regionCode
     * @return $this
     */
    public function setRegionCode(string $regionCode): AddressInterface
    {
        return $this->setData(self::REGION_CODE, $regionCode);
    }

    /**
     * Set Country Code
     *
     * @param string $countryCode
     * @return $this
     */
    public function setCountryCode(string $countryCode): AddressInterface
    {
        return $this->setData(self::COUNTRY_CODE, $countryCode);
    }

    /**
     * Set street
     *
     * @param string[] $street
     * @return $this
     */
    public function setStreet(array $street): AddressInterface
    {
        return $this->setData(self::STREET, $street);
    }

    /**
     * Set telephone number
     *
     * @param string $telephone
     * @return $this
     */
    public function setTelephone(string $telephone): AddressInterface
    {
        return $this->setData(self::TELEPHONE, trim($telephone));
    }

    /**
     * Set fax number
     *
     * @param string $fax
     * @return $this
     */
    public function setFax(string $fax): AddressInterface
    {
        return $this->setData(self::FAX, $fax);
    }

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return $this
     */
    public function setPostcode(string $postcode): AddressInterface
    {
        return $this->setData(self::POSTCODE, $postcode);
    }

    /**
     * Set city name
     *
     * @param string $city
     * @return $this
     */
    public function setCity(string $city): AddressInterface
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * Set if this address is default shipping address.
     *
     * @param bool $isDefaultShipping
     * @return $this
     */
    public function setIsDefaultShipping(bool $isDefaultShipping): AddressInterface
    {
        return $this->setData(self::DEFAULT_SHIPPING, $isDefaultShipping);
    }

    /**
     * Set if this address is default billing address
     *
     * @param bool $isDefaultBilling
     * @return $this
     */
    public function setIsDefaultBilling(bool $isDefaultBilling): AddressInterface
    {
        return $this->setData(self::DEFAULT_BILLING, $isDefaultBilling);
    }
}
