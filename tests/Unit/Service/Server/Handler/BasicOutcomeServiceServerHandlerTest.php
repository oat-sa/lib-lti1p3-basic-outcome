<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\Lti1p3BasicOutcome\Tests\Unit\Service\Server\Handler;

use OAT\Library\Lti1p3BasicOutcome\Factory\Response\BasicOutcomeResponseFactory;
use OAT\Library\Lti1p3BasicOutcome\Generator\BasicOutcomeMessageIdentifierGeneratorInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\BasicOutcomeMessageInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\Request\BasicOutcomeRequest;
use OAT\Library\Lti1p3BasicOutcome\Message\Response\BasicOutcomeResponseInterface;
use OAT\Library\Lti1p3BasicOutcome\Service\Server\Handler\BasicOutcomeServiceServerHandler;
use OAT\Library\Lti1p3BasicOutcome\Service\Server\Processor\BasicOutcomeServiceServerProcessorInterface;
use OAT\Library\Lti1p3BasicOutcome\Service\Server\Processor\BasicOutcomeServiceServerProcessorResult;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use PHPUnit\Framework\TestCase;

class BasicOutcomeServiceServerHandlerTest extends TestCase
{
    /** @var BasicOutcomeServiceServerHandler */
    private $subject;

    protected function setUp(): void
    {
        $generatorMock = $this->createMock(BasicOutcomeMessageIdentifierGeneratorInterface::class);
        $generatorMock
            ->expects($this->any())
            ->method('generate')
            ->willReturn('respId');

        $this->subject = new BasicOutcomeServiceServerHandler(
            $this->createTestProcessor(),
            new BasicOutcomeResponseFactory($generatorMock)
        );
    }

    public function testReadResultHandlingSuccess(): void
    {
        $request = new BasicOutcomeRequest(
            'reqId',
            BasicOutcomeMessageInterface::TYPE_READ_RESULT,
            'sourcedId'
        );

        $response = $this->subject->handle($request);

        $this->assertInstanceOf(BasicOutcomeResponseInterface::class, $response);

        $this->assertEquals('respId', $response->getIdentifier());
        $this->assertEquals(BasicOutcomeMessageInterface::TYPE_READ_RESULT, $response->getType());
        $this->assertTrue($response->isSuccess());
        $this->assertEquals('reqId', $response->getReferenceRequestIdentifier());
        $this->assertEquals(BasicOutcomeMessageInterface::TYPE_READ_RESULT, $response->getReferenceRequestType());
        $this->assertEquals('read message', $response->getDescription());
        $this->assertEquals(0.42, $response->getScore());
        $this->assertEquals('en', $response->getLanguage());
    }

    public function testReplaceResultHandlingSuccess(): void
    {
        $request = new BasicOutcomeRequest(
            'reqId',
            BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT,
            'sourcedId',
            0.42,
            'en'
        );

        $response = $this->subject->handle($request);

        $this->assertInstanceOf(BasicOutcomeResponseInterface::class, $response);

        $this->assertEquals('respId', $response->getIdentifier());
        $this->assertEquals(BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT, $response->getType());
        $this->assertTrue($response->isSuccess());
        $this->assertEquals('reqId', $response->getReferenceRequestIdentifier());
        $this->assertEquals(BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT, $response->getReferenceRequestType());
        $this->assertEquals('replace message', $response->getDescription());
        $this->assertNull($response->getScore());
        $this->assertNull($response->getLanguage());
    }

    public function testDeleteResultHandlingSuccess(): void
    {
        $request = new BasicOutcomeRequest(
            'reqId',
            BasicOutcomeMessageInterface::TYPE_DELETE_RESULT,
            'sourcedId'
        );

        $response = $this->subject->handle($request);

        $this->assertInstanceOf(BasicOutcomeResponseInterface::class, $response);

        $this->assertEquals('respId', $response->getIdentifier());
        $this->assertEquals(BasicOutcomeMessageInterface::TYPE_DELETE_RESULT, $response->getType());
        $this->assertTrue($response->isSuccess());
        $this->assertEquals('reqId', $response->getReferenceRequestIdentifier());
        $this->assertEquals(BasicOutcomeMessageInterface::TYPE_DELETE_RESULT, $response->getReferenceRequestType());
        $this->assertEquals('delete message', $response->getDescription());
        $this->assertNull($response->getScore());
        $this->assertNull($response->getLanguage());
    }

    public function testHandlingFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Error during basic outcome server handling: unsupported basic outcome type invalid');

        $request = new BasicOutcomeRequest(
            'reqId',
            'invalid',
            'sourcedId'
        );

        $this->subject->handle($request);
    }

    private function createTestProcessor(): BasicOutcomeServiceServerProcessorInterface
    {
        return new class () implements BasicOutcomeServiceServerProcessorInterface
        {
            public function processReadResult(string $sourcedId): BasicOutcomeServiceServerProcessorResult
            {
                return new BasicOutcomeServiceServerProcessorResult(
                    true,
                    'read message',
                    0.42,
                    'en'
                );
            }

            public function processReplaceResult(string $sourcedId, float $score, string $language = 'en'): BasicOutcomeServiceServerProcessorResult
            {
                return new BasicOutcomeServiceServerProcessorResult(
                    true,
                    'replace message'
                );
            }

            public function processDeleteResult(string $sourcedId): BasicOutcomeServiceServerProcessorResult
            {
                return new BasicOutcomeServiceServerProcessorResult(
                    true,
                    'delete message'
                );
            }
        };
    }
}
