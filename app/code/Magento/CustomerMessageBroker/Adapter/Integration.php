<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerMessageBroker\Adapter;

use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Integration\Model\Integration as IntegrationModel;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\DeploymentConfig;

/**
 * Manage API integration used for messageBroker
 */
class Integration
{
    /**
     * The name of the integration
     */
    private const NAME = 'customerMessageBroker';

    /**
     * Config array path in env.php
     */
    private const TOKEN_CONFIG_PATH = 'integration/' . self::NAME . '/access_token';

    /**
     * @var IntegrationServiceInterface
     */
    private $integrationService;

    /**
     * @var OauthServiceInterface
     */
    private $oauthService;

    /**
     * @var Writer
     */
    private $deploymentConfigWriter;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfigReader;

    /**
     * @param IntegrationServiceInterface $integrationService
     * @param OauthServiceInterface $oauthService
     * @param DeploymentConfig $deploymentConfigReader
     * @param Writer $deploymentConfigWriter
     */
    public function __construct(
        IntegrationServiceInterface $integrationService,
        OauthServiceInterface $oauthService,
        DeploymentConfig $deploymentConfigReader,
        Writer $deploymentConfigWriter
    ) {
        $this->integrationService = $integrationService;
        $this->oauthService = $oauthService;
        $this->deploymentConfigReader = $deploymentConfigReader;
        $this->deploymentConfigWriter = $deploymentConfigWriter;
    }

    /**
     * Activate integration and generate token
     *
     * Only if integration is not active or token is not created
     *
     * @return IntegrationModel
     * @throws FileSystemException
     * @throws IntegrationException
     */
    public function activateIntegration()
    {
        $integration = $this->integrationService->findByName(self::NAME);
        if ($integration->getStatus() != IntegrationModel::STATUS_ACTIVE) {
            if ($this->oauthService->createAccessToken($integration->getConsumerId(), true)) {
                $integration->setStatus(IntegrationModel::STATUS_ACTIVE);
                $this->integrationService->update($integration->getData());
            }
        }
        $token = $this->oauthService->getAccessToken($integration->getConsumerId());
        $this->persistToken($token->getToken());

        return $integration;
    }

    /**
     * Get integration access token
     *
     * @return string
     * @throws FileSystemException
     * @throws IntegrationException
     */
    public function getIntegrationAccessToken()
    {
        $token = $this->deploymentConfigReader->get(self::TOKEN_CONFIG_PATH);

        if (empty($token)) {
            $this->activateIntegration();
            $token = $this->deploymentConfigReader->get(self::TOKEN_CONFIG_PATH);
        }

        return $token;
    }

    /**
     * Persist integration access token to env.php
     *
     * @param string $token
     * @throws FileSystemException
     */
    private function persistToken(string $token): void
    {
        $this->deploymentConfigWriter->saveConfig(
            [
                ConfigFilePool::APP_ENV => [
                    'integration' => [
                        self::NAME => ['access_token' => $token]
                    ]
                ]
            ]
        );
    }
}
