<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerMessageBroker\Queue;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test message generator
 */
class MessageGeneratorTest extends TestCase
{
    /**
     * @var MessageGenerator
     */
    private $messageGenerator;

    protected function setup()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->messageGenerator = $objectManager->get(MessageGenerator::class);
    }

    public function testGenerate()
    {
        $messageData = [
            'id' => '9',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'addresses' => [
                [
                    'street' => '123 Fake Street',
                    'city' => 'Austin',
                    'region' => 'TX'
                ]
            ]
        ];
        $metadata = [
            'entity_type' => 'customer',
            'action' => 'save'
        ];

        $message = $this->messageGenerator->generate($messageData, $metadata);

        $this->assertEquals($messageData, $message['data']);
        $this->assertEquals('customer', $message['entity_type']);
        $this->assertEquals('save', $message['action']);
    }

    public function testGenerateSerialized()
    {
        $messageData = [
            'id' => '9',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'addresses' => [
                [
                    'street' => '123 Fake Street',
                    'city' => 'Austin',
                    'region' => 'TX'
                ]
            ]
        ];
        $metadata = [
            'entity_type' => 'customer',
            'action' => 'save'
        ];

        $message = $this->messageGenerator->generateSerialized($messageData, $metadata);

        $this->assertTrue(is_string($message));
        $this->assertEquals(
            '{"entity_type":"customer","action":"save","data":{"id":"9","firstname":"John","lastname":"Doe",'
            . '"addresses":[{"street":"123 Fake Street","city":"Austin","region":"TX"}]}}',
            $message
        );
    }
}
