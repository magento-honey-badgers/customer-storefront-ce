<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
    const DATE_OF_BIRTH = 'dob';
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
    public function getId(): ?int;

    /**
     * Set customer id
     *
     * @param int $id
     * @return $this
     */
    public function setId(int $id): CustomerInterface;

    /**
     * Get first name
     *
     * @return string|null
     */
    public function getFirstname(): ?string;

    /**
     * Set first name
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname(string $firstname): CustomerInterface;
    /**
     * Get last name
     *
     * @return string|null
     */
    public function getLastname(): ?string;

    /**
     * Set last name
     *
     * @param string $lastname
     * @return $this
     */
    public function setLastname(string $lastname): CustomerInterface;

    /**
     * Get last name
     *
     * @return string|null
     */
    public function getMiddlename(): ?string;

    /**
     * Set last name
     *
     * @param string $middlename
     * @return $this
     */
    public function setMiddlename(string $middlename): CustomerInterface;

    /**
     * Get date of birth
     *
     * @return string|null
     */
    public function getDateOfBirth(): ?string;

    /**
     * Set date of birth
     *
     * @param string $dateOfBirth
     * @return $this
     */
    public function setDateOfBirth(string $dateOfBirth): CustomerInterface;

    /**
     * Get email address
     *
     * @return string|null
     */
    public function getEmail(): ?string;

    /**
     * Set email address
     *
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): CustomerInterface;

    /**
     * Get gender
     *
     * @return int|null
     */
    public function getGender(): ?int;

    /**
     * Set gender
     *
     * @param int $gender
     * @return $this
     */
    public function setGender(int $gender): CustomerInterface;

    /**
     * Get prefix
     *
     * @return string|null
     */
    public function getPrefix(): ?string;

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix(string $prefix): CustomerInterface;

    /**
     * Get suffix
     *
     * @return string|null
     */
    public function getSuffix(): ?string;

    /**
     * Set suffix
     *
     * @param string $suffix
     * @return $this
     */
    public function setSuffix(string $suffix): CustomerInterface;

    /**
     * Get store id
     *
     * @return int|null
     */
    public function getStoreId(): ?int;

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId(int $storeId): CustomerInterface;

    /**
     * Get website id
     *
     * @return int|null
     */
    public function getWebsiteId(): ?int;

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId(int $websiteId): CustomerInterface;

    /**
     * Get tax Vat
     *
     * @return string|null
     */
    public function getTaxvat(): ?string;

    /**
     * Set tax Vat
     *
     * @param string $taxvat
     * @return $this
     */
    public function setTaxvat(string $taxvat): CustomerInterface;

    /**
     * Get default billing address id
     *
     * @return int|null
     */
    public function getDefaultBilling(): ?int;

    /**
     * Set default billing address id
     *
     * @param int $defaultBilling
     * @return $this
     */
    public function setDefaultBilling(int $defaultBilling): CustomerInterface;

    /**
     * Get default shipping address id
     *
     * @return int|null
     */
    public function getDefaultShipping(): ?int;

    /**
     * Set default shipping address id
     *
     * @param int $defaultShipping
     * @return $this
     */
    public function setDefaultShipping(int $defaultShipping): CustomerInterface;

    /**
     * Get customer addresses.
     *
     * @return AddressInterface[]|null
     */
    public function getAddresses(): ?array;

    /**
     * Set customer addresses.
     *
     * @param AddressInterface[] $addresses
     * @return $this
     */
    public function setAddresses(array $addresses): CustomerInterface;
}
