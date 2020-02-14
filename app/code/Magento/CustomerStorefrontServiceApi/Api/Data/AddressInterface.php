<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontServiceApi\Api\Data;

/**
 * Storefront API interface for the customer address.
 */
interface AddressInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const CUSTOMER_ID = 'customer_id';
    const FIRSTNAME = 'firstname';
    const LASTNAME = 'lastname';
    const REGION = 'region';
    const REGION_CODE = 'region_code';
    const COUNTRY_CODE = 'country_id';
    const STREET = 'street';
    const TELEPHONE = 'telephone';
    const FAX = 'fax';
    const POSTCODE = 'postcode';
    const CITY = 'city';
    const DEFAULT_BILLING = 'default_billing';
    const DEFAULT_SHIPPING = 'default_shipping';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId(int $id): AddressInterface;

    /**
     * Get customer ID
     *
     * @return int|null
     */
    public function getCustomerId(): ?int;

    /**
     * Set customer ID
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId(int $customerId): AddressInterface;

    /**
     * Get first name
     *
     * @return string|null
     */
    public function getFirstname(): ?string;

    /**
     * Set first name
     *
     * @param string $firstName
     * @return $this
     */
    public function setFirstname(string $firstName): AddressInterface;

    /**
     * Get last name
     *
     * @return string|null
     */
    public function getLastname(): ?string;

    /**
     * Set last name
     *
     * @param string $lastName
     * @return $this
     */
    public function setLastname(string $lastName): AddressInterface;

    /**
     * Get region
     *
     * @return RegionInterface|null
     */
    public function getRegion(): ?RegionInterface;

    /**
     * Set region
     *
     * @param RegionInterface $region
     * @return $this
     */
    public function setRegion(RegionInterface $region): AddressInterface;

    /**
     * Get region Code
     *
     * @return string|null
     */
    public function getRegionCode(): ?string;

    /**
     * Set region Code
     *
     * @param string $regionCode
     * @return $this
     */
    public function setRegionCode(string $regionCode): AddressInterface;

    /**
     * Two-letter country code in ISO_3166-2 format
     *
     * @return string|null
     */
    public function getCountryCode();

    /**
     * Set country code
     *
     * @param string $countryCode
     * @return $this
     */
    public function setCountryCode(string $countryCode): AddressInterface;

    /**
     * Get street
     *
     * @return string[]|null
     */
    public function getStreet(): ?array;

    /**
     * Set street
     *
     * @param string[] $street
     * @return $this
     */
    public function setStreet(array $street): AddressInterface;

    /**
     * Get telephone number
     *
     * @return string|null
     */
    public function getTelephone(): ?string;

    /**
     * Set telephone number
     *
     * @param string $telephone
     * @return $this
     */
    public function setTelephone(string $telephone): AddressInterface;

    /**
     * Get fax number
     *
     * @return string|null
     */
    public function getFax(): ?string;

    /**
     * Set fax number
     *
     * @param string $fax
     * @return $this
     */
    public function setFax(string $fax): AddressInterface;

    /**
     * Get postcode
     *
     * @return string|null
     */
    public function getPostcode(): ?string;

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return $this
     */
    public function setPostcode(string $postcode): AddressInterface;

    /**
     * Get city name
     *
     * @return string|null
     */
    public function getCity(): ?string;

    /**
     * Set city name
     *
     * @param string $city
     * @return $this
     */
    public function setCity(string $city): AddressInterface;

    /**
     * Get if this address is default shipping address.
     *
     * @return bool
     */
    public function isDefaultShipping(): bool;

    /**
     * Set if this address is default shipping address.
     *
     * @param bool $isDefaultShipping
     * @return $this
     */
    public function setIsDefaultShipping(bool $isDefaultShipping): AddressInterface;

    /**
     * Get if this address is default billing address
     *
     * @return bool
     */
    public function isDefaultBilling(): ?bool;

    /**
     * Set if this address is default billing address
     *
     * @param bool $isDefaultBilling
     * @return $this
     */
    public function setIsDefaultBilling(bool $isDefaultBilling): AddressInterface;
}
