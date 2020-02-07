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
     * @return string|null
     */
    public function getDefaultBilling()
    {
        return $this->_get(self::DEFAULT_BILLING);
    }

    /**
     * Get default shipping address id
     *
     * @return string|null
     */
    public function getDefaultShipping()
    {
        return $this->_get(self::DEFAULT_SHIPPING);
    }

    /**
     * Get created at time
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Get email address
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->_get(self::EMAIL);
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->_get(self::FIRSTNAME);
    }

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->_get(self::LASTNAME);
    }

    /**
     * Get addresses
     *
     * @return AddressInterface[]|null
     */
    public function getAddresses()
    {
        return $this->_get(self::KEY_ADDRESSES);
    }

    /**
     * Get date of birth
     *
     * @return mixed|string|null
     */
    public function getDateOfBirth()
    {
        return $this->_get(self::DATE_OF_BIRTH);
    }

    /**
     * Set customer id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Set default billing address id
     *
     * @param string $defaultBilling
     * @return $this
     */
    public function setDefaultBilling(string $defaultBilling)
    {
        return $this->setData(self::DEFAULT_BILLING, $defaultBilling);
    }

    /**
     * Set default shipping address id
     *
     * @param string $defaultShipping
     * @return $this
     */
    public function setDefaultShipping(string $defaultShipping)
    {
        return $this->setData(self::DEFAULT_SHIPPING, $defaultShipping);
    }

    /**
     * Set created at time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Set email address
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Set first name
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname(string $firstname)
    {
        return $this->setData(self::FIRSTNAME, $firstname);
    }

    /**
     * Set last name
     *
     * @param string $lastname
     * @return string
     */
    public function setLastname($lastname)
    {
        return $this->setData(self::LASTNAME, $lastname);
    }

    /**
     * Get middle name
     *
     * @return string|null
     */
    public function getMiddlename()
    {
        return $this->_get(self::MIDDLENAME);
    }

    /**
     * Set middle name
     *
     * @param string $middlename
     * @return CustomerInterface
     */
    public function setMiddlename($middlename)
    {
        return $this->setData(self::MIDDLENAME, $middlename);
    }

    /**
     * Get gender
     *
     * @return string|null
     */
    public function getGender()
    {
        return $this->_get(self::GENDER);
    }

    /**
     * Set gender
     *
     * @param string $gender
     * @return Customer
     */
    public function setGender($gender)
    {
        return $this->setData(self::GENDER, $gender);
    }

    /**
     * Get prefix
     *
     * @return string|null
     */
    public function getPrefix()
    {
        return $this->_get(self::PREFIX);
    }

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return  Customer
     */
    public function setPrefix($prefix)
    {
        return $this->setData(self::PREFIX, $prefix);
    }

    /**
     * Get suffix
     *
     * @return string|null
     */
    public function getSuffix()
    {
        return $this->_get(self::SUFFIX);
    }

    /**
     * Set suffix
     *
     * @param string $suffix
     * @return  Customer
     */
    public function setSuffix($suffix)
    {
        return $this->setData(self::SUFFIX, $suffix);
    }

    /**
     * Set store id
     *
     * @param int $storeId
     * @return  Customer
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Get store id
     *
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * Get website id
     *
     * @return int|null
     */
    public function getWebsiteId()
    {
        return $this->_get(self::WEBSITE_ID);
    }

    /**
     * Get tax Vat.
     *
     * @return string|null
     */
    public function getTaxvat()
    {
        return $this->_get(self::TAXVAT);
    }

    /**
     * Set tax Vat
     *
     * @param string $taxvat
     * @return CustomerInterface
     */
    public function setTaxvat($taxvat)
    {
        return $this->setData(self::TAXVAT, $taxvat);
    }

    /**
     * Set customer addresses.
     *
     * @param AddressInterface[] $addresses
     * @return $this
     */
    public function setAddresses(array $addresses = null)
    {
        return $this->setData(self::KEY_ADDRESSES, $addresses);
    }

    /**
     * Set data of birth
     *
     * @param string $dateOfbirth
     * @return Customer|CustomerInterface
     */
    public function setDateOfBirth(string $dateOfbirth)
    {
        return $this->setData(self::DATE_OF_BIRTH, $dateOfbirth);
    }
}
