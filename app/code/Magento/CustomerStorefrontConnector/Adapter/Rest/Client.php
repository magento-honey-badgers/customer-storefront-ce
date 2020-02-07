<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerStorefrontConnector\Adapter\Rest;

use Magento\CustomerStorefrontConnector\Adapter\Integration;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Backend\Model\UrlInterface;

/**
 * Client for REST API requests
 */
class Client
{
    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var Integration
     */
    private $integration;

    /**
     * @var UrlInterface
     */
    private $urlHelper;

    /**
     * @param CurlFactory $curlFactory
     * @param Integration $integration
     * @param UrlInterface $urlHelper
     */
    public function __construct(
        CurlFactory $curlFactory,
        Integration $integration,
        UrlInterface $urlHelper
    ) {
        $this->curlFactory = $curlFactory;
        $this->integration = $integration;
        $this->urlHelper = $urlHelper;
    }

    /**
     * Make HTTP request
     *
     * @param string $route
     * @return array
     */
    public function sendRequest(string $route): array
    {
        $baseUrl = $this->urlHelper->getRouteUrl();

        $authorizationToken = $this->integration->getIntegrationAccessToken();
        $curl = $this->curlFactory->create();
        $curl->addHeader('Authorization', 'Bearer ' . $authorizationToken);
        $curl->addHeader('accept', 'application/json');
        $curl->get($baseUrl . $route);

        return [
            'status' => $curl->getStatus(),
            'body' => $curl->getBody()
        ];
    }
}
