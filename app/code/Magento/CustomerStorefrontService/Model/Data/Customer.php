<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\Data;

use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Customer DTO class
 */
class Customer extends AbstractSimpleObject implements CustomerInterface
{
    /**
     * Get default billing
     *
     * @return int|null
     */
    public function getDefaultBilling(): ?int
    {
        return $this->_get(self::DEFAULT_BILLING);
    }

    /**
     * Get default shipping address id
     *
     * @return int|null
     */
    public function getDefaultShipping(): ?int
    {
        return $this->_get(self::DEFAULT_SHIPPING);
    }

    /**
     * Get email address
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->_get(self::EMAIL);
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
     * Get customer id
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->_get(self::ID);
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
     * Get middle name
     *
     * @return string|null
     */
    public function getMiddlename(): ?string
    {
        return $this->_get(self::MIDDLENAME);
    }

    /**
     * Get gender
     *
     * @return int|null
     */
    public function getGender(): ?int
    {
        return $this->_get(self::GENDER);
    }

    /**
     * Get addresses
     *
     * @return AddressInterface[]|null
     */
    public function getAddresses(): ?array
    {
        return $this->_get(self::KEY_ADDRESSES);
    }

    /**
     * Get date of birth
     *
     * @return string|null
     */
    public function getDateOfBirth(): ?string
    {
        return $this->_get(self::DATE_OF_BIRTH);
    }

    /**
     * Get prefix
     *
     * @return string|null
     */
    public function getPrefix(): ?string
    {
        return $this->_get(self::PREFIX);
    }

    /**
     * Get suffix
     *
     * @return string|null
     */
    public function getSuffix(): ?string
    {
        return $this->_get(self::SUFFIX);
    }

    /**
     * Get store id
     *
     * @return int|null
     */
    public function getStoreId(): ?int
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * Get website id
     *
     * @return int|null
     */
    public function getWebsiteId(): ?int
    {
        return $this->_get(self::WEBSITE_ID);
    }

    /**
     * Get tax Vat.
     *
     * @return string|null
     */
    public function getTaxvat(): ?string
    {
        return $this->_get(self::TAXVAT);
    }

    /**
     * Set customer id
     *
     * @param int $id
     * @return $this
     */
    public function setId(int $id): CustomerInterface
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Set default billing address id
     *
     * @param int $defaultBilling
     * @return $this
     */
    public function setDefaultBilling(int $defaultBilling): CustomerInterface
    {
        return $this->setData(self::DEFAULT_BILLING, $defaultBilling);
    }

    /**
     * Set default shipping address id
     *
     * @param int $defaultShipping
     * @return $this
     */
    public function setDefaultShipping(int $defaultShipping): CustomerInterface
    {
        return $this->setData(self::DEFAULT_SHIPPING, $defaultShipping);
    }

    /**
     * Set email address
     *
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): CustomerInterface
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Set first name
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname(string $firstname): CustomerInterface
    {
        return $this->setData(self::FIRSTNAME, $firstname);
    }

    /**
     * Set last name
     *
     * @param string $lastname
     * @return $this
     */
    public function setLastname(string $lastname): CustomerInterface
    {
        return $this->setData(self::LASTNAME, $lastname);
    }

    /**
     * Set middle name
     *
     * @param string $middlename
     * @return $this
     */
    public function setMiddlename(string $middlename): CustomerInterface
    {
        return $this->setData(self::MIDDLENAME, $middlename);
    }

    /**
     * Set gender
     *
     * @param int $gender
     * @return $this
     */
    public function setGender(int $gender): CustomerInterface
    {
        return $this->setData(self::GENDER, $gender);
    }

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix(string $prefix): CustomerInterface
    {
        return $this->setData(self::PREFIX, $prefix);
    }

    /**
     * Set suffix
     *
     * @param string $suffix
     * @return $this
     */
    public function setSuffix(string $suffix): CustomerInterface
    {
        return $this->setData(self::SUFFIX, $suffix);
    }

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId(int $storeId): CustomerInterface
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId(int $websiteId): CustomerInterface
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * Set tax Vat
     *
     * @param string $taxvat
     * @return $this
     */
    public function setTaxvat(string $taxvat): CustomerInterface
    {
        return $this->setData(self::TAXVAT, $taxvat);
    }

    /**
     * Set customer addresses.
     *
     * @param AddressInterface[] $addresses
     * @return $this
     */
    public function setAddresses(array $addresses = []): CustomerInterface
    {
        return $this->setData(self::KEY_ADDRESSES, $addresses);
    }

    /**
     * Set data of birth
     *
     * @param string $dateOfBirth
     * @return $this
     */
    public function setDateOfBirth(string $dateOfBirth): CustomerInterface
    {
        return $this->setData(self::DATE_OF_BIRTH, $dateOfBirth);
    }

    /**
     * Get custom attributes.
     *
     * @return array|null
     */
    public function getCustomAttributes(): ?array
    {
        return $this->_get(self::CUSTOM_ATTRIBUTES);
    }

    /**
     * Set custom attributes.
     *
     * @param array $customAttributes
     * @return $this
     */
    public function setCustomAttributes(array $customAttributes): CustomerInterface
    {
        return $this->setData(self::CUSTOM_ATTRIBUTES, $customAttributes);
    }

    /**
     * Get extension attributes.
     *
     * @return array|null
     */
    public function getExtensionAttributes(): ?array
    {
        return $this->_get(self::EXTENSION_ATTRIBUTES);
    }

    /**
     * Set extension attributes.
     *
     * @param array $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(array $extensionAttributes): CustomerInterface
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES, $extensionAttributes);
    }
}
