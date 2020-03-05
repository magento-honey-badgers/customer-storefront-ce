<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQlAdapter\Model\Context;

use Magento\Authorization\Model\UserContextInterface;
use Magento\GraphQl\Model\Query\ContextParametersInterface;
use Magento\GraphQl\Model\Query\ContextParametersProcessorInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * @inheritdoc
 */
class UserStatusChecker implements ContextParametersProcessorInterface
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @param UserContextInterface $userContext
     * @param AuthenticationInterface $authentication
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     */
    public function __construct(
        UserContextInterface $userContext,
        AuthenticationInterface $authentication,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement
    ) {
        $this->userContext = $userContext;
        $this->authentication = $authentication;
        $this->customerRepository = $customerRepository;
        $this->accountManagement = $accountManagement;
    }

    /**
     * @inheritdoc
     */
    public function execute(ContextParametersInterface $contextParameters): ContextParametersInterface
    {
        $currentUserId = $this->userContext->getUserId();
        $currentUserType = $this->userContext->getUserType();
        if (null !== $currentUserId) {
            $currentUserId = (int)$currentUserId;
        }
        if (null !== $currentUserType) {
            $currentUserType = (int)$currentUserType;
        }

        if (false !== $this->isCustomer($currentUserId, $currentUserType)) {
            if (true === $this->authentication->isLocked($currentUserId)) {
                throw new GraphQlAuthenticationException(__('The account is locked.'));
            }

            try {
                $confirmationStatus = $this->accountManagement->getConfirmationStatus($currentUserId);
                // @codeCoverageIgnoreStart
            } catch (LocalizedException $e) {
                throw new GraphQlInputException(__($e->getMessage()));
                // @codeCoverageIgnoreEnd
            }

            if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                throw new GraphQlAuthenticationException(__("This account isn't confirmed. Verify and try again."));
            }
        }
        return $contextParameters;
    }

    /**
     * Checking if current user is logged
     *
     * @param int|null $customerId
     * @param int|null $customerType
     * @return bool
     */
    private function isCustomer(?int $customerId, ?int $customerType): bool
    {
        return !empty($customerId) && !empty($customerType) && $customerType !== UserContextInterface::USER_TYPE_GUEST;
    }
}
