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
interface CustomerInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const FIRSTNAME = 'firstname';
    const LASTNAME = 'lastname';
    const MIDDLENAME = 'middlename';
    const DATE_OF_BIRTH = 'date_of_birth';
    const EMAIL = 'email';
    const GENDER = 'gender';
    const PREFIX = 'prefix';
    const SUFFIX = 'suffix';
    const STORE_ID = 'store_id';
    const WEBSITE_ID = 'website_id';
    const TAXVAT = 'taxvat';
    const DEFAULT_BILLING = 'default_billing';
    const DEFAULT_SHIPPING = 'default_shipping';
    const KEY_ADDRESSES = 'addresses';

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set customer id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname();

    /**
     * Set first name
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname(string $firstname);
    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname();

    /**
     * Set last name
     *
     * @param string $lastname
     * @return $this
     */
    public function setLastname($lastname);

    /**
     * Get last name
     *
     * @return string
     */
    public function getMiddlename();

    /**
     * Set last name
     *
     * @param string $middlename
     * @return $this
     */
    public function setMiddlename($middlename);

    /**
     * Get date of birth
     *
     * @return string|null
     */
    public function getDateOfBirth();

    /**
     * Set date of birth
     *
     * @param string $dateOfbirth
     * @return $this
     */
    public function setDateOfBirth(string $dateOfbirth);

    /**
     * Get email address
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email address
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get gender
     *
     * @return int|null
     */
    public function getGender();

    /**
     * Set gender
     *
     * @param int $gender
     * @return $this
     */
    public function setGender($gender);

    /**
     * Get prefix
     *
     * @return string|null
     */
    public function getPrefix();

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix);

    /**
     * Get suffix
     *
     * @return string|null
     */
    public function getSuffix();

    /**
     * Set suffix
     *
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix);

    /**
     * Get store id
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get website id
     *
     * @return int|null
     */
    public function getWebsiteId();

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId);

    /**
     * Get tax Vat
     *
     * @return string|null
     */
    public function getTaxvat();

    /**
     * Set tax Vat
     *
     * @param string $taxvat
     * @return $this
     */
    public function setTaxvat($taxvat);

    /**
     * Get default billing address id
     *
     * @return string|null
     */
    public function getDefaultBilling();

    /**
     * Set default billing address id
     *
     * @param string $defaultBilling
     * @return $this
     */
    public function setDefaultBilling(string $defaultBilling);

    /**
     * Get default shipping address id
     *
     * @return string|null
     */
    public function getDefaultShipping();

    /**
     * Set default shipping address id
     *
     * @param string $defaultShipping
     * @return $this
     */
    public function setDefaultShipping(string $defaultShipping);

    /**
     * Get customer addresses.
     *
     * @return AddressInterface[]|null
     */
    public function getAddresses();

    /**
     * Set customer addresses.
     *
     * @param AddressInterface[] $addresses
     * @return $this
     */
    public function setAddresses(array $addresses = null);
}
