<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontService\Model\Data;

use Magento\CustomerStorefrontServiceApi\Api\Data\RegionInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Data Model implementing Address Region interface
 */
class Region extends AbstractSimpleObject implements RegionInterface
{
    /**
     * Get region code
     *
     * @return string|null
     */
    public function getRegionCode(): ?string
    {
        return $this->_get(self::REGION_CODE);
    }

    /**
     * Get region
     *
     * @return string|null
     */
    public function getRegion(): ?string
    {
        return $this->_get(self::REGION);
    }

    /**
     * Get region id
     *
     * @return int|null
     */
    public function getRegionId(): ?int
    {
        return $this->_get(self::REGION_ID);
    }

    /**
     * Set region code
     *
     * @param string $regionCode
     * @return $this
     */
    public function setRegionCode(string $regionCode): RegionInterface
    {
        return $this->setData(self::REGION_CODE, $regionCode);
    }

    /**
     * Set region
     *
     * @param string $region
     * @return $this
     */
    public function setRegion(string $region): RegionInterface
    {
        return $this->setData(self::REGION, $region);
    }

    /**
     * Set region id
     *
     * @param int $regionId
     * @return $this
     */
    public function setRegionId(int $regionId): RegionInterface
    {
        return $this->setData(self::REGION_ID, $regionId);
    }
}
