<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontServiceApi\Api\Data;

/**
 * Storefront API interface for the customer address region interface.
 */
interface RegionInterface
{
    /**
     * Constants for keys of data array. Identical to the getters in snake case
     */
    const REGION_CODE = 'region_code';
    const REGION = 'region';
    const REGION_ID = 'region_id';

    /**
     * Get region code
     *
     * @return string|null
     */
    public function getRegionCode(): ?string;

    /**
     * Set region code
     *
     * @param string $regionCode
     * @return $this
     */
    public function setRegionCode(string $regionCode): RegionInterface;

    /**
     * Get region
     *
     * @return string|null
     */
    public function getRegion(): ?string;

    /**
     * Set region
     *
     * @param string $region
     * @return $this
     */
    public function setRegion(string $region): RegionInterface;

    /**
     * Get region id
     *
     * @return int|null
     */
    public function getRegionId(): ?int;

    /**
     * Set region id
     *
     * @param int $regionId
     * @return $this
     */
    public function setRegionId(int $regionId): RegionInterface;
}
