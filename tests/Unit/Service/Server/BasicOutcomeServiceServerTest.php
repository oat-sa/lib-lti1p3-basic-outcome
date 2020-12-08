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

namespace OAT\Library\Lti1p3BasicOutcome\Tests\Unit\Service\Server;

use Exception;
use Nyholm\Psr7\ServerRequest;
use OAT\Library\Lti1p3BasicOutcome\Message\BasicOutcomeMessageInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\Request\BasicOutcomeRequest;
use OAT\Library\Lti1p3BasicOutcome\Message\Response\BasicOutcomeResponse;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Response\BasicOutcomeResponseSerializer;
use OAT\Library\Lti1p3BasicOutcome\Service\Server\BasicOutcomeServiceServer;
use OAT\Library\Lti1p3BasicOutcome\Service\Server\Handler\BasicOutcomeServiceServerHandler;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidationResult;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use OAT\Library\Lti1p3Core\Tests\Resource\Logger\TestLogger;
use OAT\Library\Lti1p3Core\Tests\Traits\DomainTestingTrait;
use OAT\Library\Lti1p3Core\Tests\Traits\NetworkTestingTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class BasicOutcomeServiceServerTest extends TestCase
{
    use DomainTestingTrait;
    use NetworkTestingTrait;

    /** @var Environment */
    private $twig;

    /** @var AccessTokenRequestValidator|MockObject */
    private $validatorMock;

    /** @var BasicOutcomeServiceServerHandler|MockObject */
    private $handlerMock;

    /** @var TestLogger */
    private $logger;

    /** @var BasicOutcomeServiceServer */
    private $subject;

    protected function setUp(): void
    {
        $this->twig = new Environment(new FilesystemLoader(__DIR__ . '/../../../../templates'));
        $this->logger = new TestLogger();

        $this->validatorMock = $this->createMock(AccessTokenRequestValidator::class);
        $this->handlerMock = $this->createMock(BasicOutcomeServiceServerHandler::class);

        $this->subject = new BasicOutcomeServiceServer(
            $this->validatorMock,
            $this->handlerMock,
            null,
            null,
            null,
            $this->logger
        );
    }

    public function testHandlingSuccess(): void
    {
        $registration = $this->createTestRegistration();
        $validationResult = new AccessTokenRequestValidationResult($registration);

        $boRequest = new BasicOutcomeRequest(
            'reqId',
            BasicOutcomeMessageInterface::TYPE_READ_RESULT,
            'sourcedId'
        );

        $boResponse = new BasicOutcomeResponse(
            'respId',
            BasicOutcomeMessageInterface::TYPE_READ_RESULT,
            true,
            'reqId',
            BasicOutcomeMessageInterface::TYPE_READ_RESULT,
            'read description',
            0.42,
            'en'
        );

        $request = new ServerRequest(
            'POST',
            'http://platform.com/basic-outcome',
            [],
            $this->twig->render('request/readResultRequest.xml.twig', ['request' => $boRequest])
        );

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($validationResult);

        $this->handlerMock
            ->expects($this->once())
            ->method('handle')
            ->with($boRequest)
            ->willReturn($boResponse);

        $response = $this->subject->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            $boResponse,
            (new BasicOutcomeResponseSerializer())->deserialize((string)$response->getBody())
        );
    }

    public function testHandlingFailureOnUnauthorized(): void
    {
        $registration = $this->createTestRegistration();
        $validationResult = new AccessTokenRequestValidationResult($registration, null, [], 'auth error');

        $request = new ServerRequest(
            'POST',
            'http://platform.com/basic-outcome'
        );

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($validationResult);

        $response = $this->subject->handle($request);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('auth error', (string)$response->getBody());
        $this->assertTrue($this->logger->hasLog('error', 'auth error'));
    }

    public function testHandlingFailureOnGenericError(): void
    {
        $registration = $this->createTestRegistration();
        $validationResult = new AccessTokenRequestValidationResult($registration);

        $boRequest = new BasicOutcomeRequest(
            'reqId',
            BasicOutcomeMessageInterface::TYPE_READ_RESULT,
            'sourcedId'
        );

        $request = new ServerRequest(
            'POST',
            'http://platform.com/basic-outcome',
            [],
            $this->twig->render('request/readResultRequest.xml.twig', ['request' => $boRequest])
        );

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($validationResult);

        $this->handlerMock
            ->expects($this->once())
            ->method('handle')
            ->willThrowException(new Exception('generic error'));

        $response = $this->subject->handle($request);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('Internal basic outcome service error', (string)$response->getBody());
        $this->assertTrue($this->logger->hasLog('error', 'generic error'));
    }
}
