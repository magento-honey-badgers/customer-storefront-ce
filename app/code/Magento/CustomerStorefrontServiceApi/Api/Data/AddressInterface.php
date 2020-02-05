<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerStorefrontServiceApi\Api\Data;

/**
 * Customer interface.
 */
interface AddressInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const CUSTOMER_ID = 'customer_id';
    const FIRSTNAME = 'firstname';
    const LASTNAME = 'lastname';
    const REGION = 'region';
    const REGION_CODE = 'region_code';
    const COUNTRY_CODE = 'country_code';
    const STREET = 'street';
    const TELEPHONE = 'telephone';
    const FAX = 'fax';
    const POSTCODE = 'postcode';
    const CITY = 'city';
    const DEFAULT_BILLING = 'default_billing';
    const DEFAULT_SHIPPING = 'default_shipping';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get customer ID
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set customer ID
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Get first name
     *
     * @return string|null
     */
    public function getFirstname();

    /**
     * Set first name
     *
     * @param string $firstName
     * @return $this
     */
    public function setFirstname($firstName);

    /**
     * Get last name
     *
     * @return string|null
     */
    public function getLastname();

    /**
     * Set last name
     *
     * @param string $lastName
     * @return $this
     */
    public function setLastname($lastName);

    /**
     * Get region
     *
     * @return \Magento\CustomerStorefrontServiceApi\Api\Data\RegionInterface|null
     */
    public function getRegion();

    /**
     * Set region
     *
     * @param \Magento\CustomerStorefrontServiceApi\Api\Data\RegionInterface $region
     * @return $this
     */
    public function setRegion(RegionInterface $region = null);

    /**
     * Get region Code
     *
     * @return int|null
     */
    public function getRegionCode();

    /**
     * Set region Code
     *
     * @param int $regionCode
     * @return $this
     */
    public function setRegionCode($regionCode);

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
    public function setCountryCode($countryCode);

    /**
     * Get street
     *
     * @return string[]|null
     */
    public function getStreet();

    /**
     * Set street
     *
     * @param string[] $street
     * @return $this
     */
    public function setStreet(array $street);

    /**
     * Get telephone number
     *
     * @return string|null
     */
    public function getTelephone();

    /**
     * Set telephone number
     *
     * @param string $telephone
     * @return $this
     */
    public function setTelephone($telephone);

    /**
     * Get fax number
     *
     * @return string|null
     */
    public function getFax();

    /**
     * Set fax number
     *
     * @param string $fax
     * @return $this
     */
    public function setFax($fax);

    /**
     * Get postcode
     *
     * @return string|null
     */
    public function getPostcode();

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode);

    /**
     * Get city name
     *
     * @return string|null
     */
    public function getCity();

    /**
     * Set city name
     *
     * @param string $city
     * @return $this
     */
    public function setCity($city);

    /**
     * Get if this address is default shipping address.
     *
     * @return bool|null
     */
    public function isDefaultShipping();

    /**
     * Set if this address is default shipping address.
     *
     * @param bool $isDefaultShipping
     * @return $this
     */
    public function setIsDefaultShipping($isDefaultShipping);

    /**
     * Get if this address is default billing address
     *
     * @return bool|null
     */
    public function isDefaultBilling();

    /**
     * Set if this address is default billing address
     *
     * @param bool $isDefaultBilling
     * @return $this
     */
    public function setIsDefaultBilling($isDefaultBilling);
}
