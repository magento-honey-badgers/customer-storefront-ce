<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\ResourceModel\Address;

use Magento\CustomerStorefrontServiceApi\Api\Data\CustomerInterface;
use Magento\CustomerStorefrontServiceApi\Api\Data\AddressInterface;

/**
 * Class DeleteRelation
 */
class DeleteRelation
{
    /**
     * Delete relation (billing and shipping) between customer and address
     *
     * @param AddressInterface $address
     * @param CustomerInterface $customer
     * @return void
     */
    public function deleteRelation(
        AddressInterface $address,
        CustomerInterface $customer
    ) {
        $toUpdate = $this->getDataToUpdate($address, $customer);

        //TODO: implement logic
//        if (!$address->getIsCustomerSaveTransaction() && !empty($toUpdate)) {
//            $address->getResource()->getConnection()->update(
//                $address->getResource()->getTable('customer_entity'),
//                $toUpdate,
//                $address->getResource()->getConnection()->quoteInto('entity_id = ?', $customer->getId())
//            );
//        }
    }

    /**
     * Return address type (billing or shipping), or null if address is not default
     *
     * @param AddressInterface $address
     * @param CustomerInterface $customer
     * @return array
     */
    private function getDataToUpdate(
        AddressInterface $address,
        CustomerInterface $customer
    ) {
        $toUpdate = [];
        if ($address->getId()) {
            if ($customer->getDefaultBilling() == $address->getId()) {
                $toUpdate[CustomerInterface::DEFAULT_BILLING] = null;
            }

            if ($customer->getDefaultShipping() == $address->getId()) {
                $toUpdate[CustomerInterface::DEFAULT_SHIPPING] = null;
            }
        }

        return $toUpdate;
    }
}
