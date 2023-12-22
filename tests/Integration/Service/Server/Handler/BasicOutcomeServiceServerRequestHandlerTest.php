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

namespace OAT\Library\Lti1p3BasicOutcome\Tests\Integration\Service\Server\Handler;

use Nyholm\Psr7\ServerRequest;
use OAT\Library\Lti1p3BasicOutcome\Factory\Response\BasicOutcomeResponseFactory;
use OAT\Library\Lti1p3BasicOutcome\Message\BasicOutcomeMessageInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\Request\BasicOutcomeRequest;
use OAT\Library\Lti1p3BasicOutcome\Message\Response\BasicOutcomeResponse;
use OAT\Library\Lti1p3BasicOutcome\Service\BasicOutcomeServiceInterface;
use OAT\Library\Lti1p3BasicOutcome\Service\Server\Handler\BasicOutcomeServiceServerRequestHandler;
use OAT\Library\Lti1p3BasicOutcome\Service\Server\Processor\BasicOutcomeServiceServerProcessorInterface;
use OAT\Library\Lti1p3BasicOutcome\Service\Server\Processor\Result\BasicOutcomeServiceServerProcessorResult;
use OAT\Library\Lti1p3Core\Security\OAuth2\Validator\RequestAccessTokenValidator;
use OAT\Library\Lti1p3Core\Security\OAuth2\Validator\Result\RequestAccessTokenValidationResult;
use OAT\Library\Lti1p3Core\Service\Server\LtiServiceServer;
use OAT\Library\Lti1p3Core\Tests\Resource\Logger\TestLogger;
use OAT\Library\Lti1p3Core\Tests\Traits\DomainTestingTrait;
use OAT\Library\Lti1p3Core\Tests\Traits\NetworkTestingTrait;
use OAT\Library\Lti1p3Core\Util\Generator\IdGeneratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class BasicOutcomeServiceServerRequestHandlerTest extends TestCase
{
    use DomainTestingTrait;
    use NetworkTestingTrait;

    /** @var Environment */
    private $twig;

    /** @var RequestAccessTokenValidator|MockObject */
    private $validatorMock;

    /** @var BasicOutcomeServiceServerProcessorInterface|MockObject */
    private $processorMock;

    /** @var IdGeneratorInterface|MockObject */
    private $generatorMock;

    /** @var TestLogger */
    private $logger;

    /** @var LtiServiceServer */
    private $server;

    protected function setUp(): void
    {
        $this->twig = new Environment(new FilesystemLoader([
            __DIR__ . '/../../../../../templates',
            __DIR__ . '/../../../../Resources/templates',
        ]));

        $this->logger = new TestLogger();

        $this->validatorMock = $this->createMock(RequestAccessTokenValidator::class);
        $this->processorMock = $this->createMock(BasicOutcomeServiceServerProcessorInterface::class);
        $this->generatorMock = $this->createMock(IdGeneratorInterface::class);

        $subject = new BasicOutcomeServiceServerRequestHandler(
            $this->processorMock,
            null,
            null,
            new BasicOutcomeResponseFactory($this->generatorMock),
            $this->logger
        );

        $this->server = new LtiServiceServer(
            $this->validatorMock,
            $subject,
            $this->logger
        );
    }

    public function testReadResultHandlingSuccess(): void
    {
        $registration = $this->createTestRegistration();
        $validationResult = new RequestAccessTokenValidationResult($registration);

        $boRequest = new BasicOutcomeRequest(
            'reqId',
            BasicOutcomeMessageInterface::TYPE_READ_RESULT,
            'sourcedId'
        );

        $boProcessorResult = new BasicOutcomeServiceServerProcessorResult(
            true,
            'read success description',
            0.42,
            'en'
        );

        $boResponse = new BasicOutcomeResponse(
            'respId',
            BasicOutcomeMessageInterface::TYPE_READ_RESULT,
            true,
            'reqId',
            BasicOutcomeMessageInterface::TYPE_READ_RESULT,
            'read success description',
            0.42,
            'en'
        );

        $request = new ServerRequest(
            'POST',
            'http://platform.com/basic-outcome',
            [
                'Content-Type' => BasicOutcomeServiceInterface::CONTENT_TYPE_BASIC_OUTCOME
            ],
            $this->twig->render('request/readResultRequest.xml.twig', ['request' => $boRequest])
        );

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($validationResult);

        $this->processorMock
            ->expects($this->once())
            ->method('processReadResult')
            ->with($registration, 'sourcedId')
            ->willReturn($boProcessorResult);

        $this->generatorMock
            ->expects($this->once())
            ->method('generate')
            ->willReturn('respId');

        $response = $this->server->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            $this->twig->render('response/readResultResponse.xml.twig', ['response' => $boResponse]),
            (string)$response->getBody()
        );

        $this->assertTrue($this->logger->hasLog('info', 'basic-outcome service success'));
    }

    public function testReplaceResultHandlingSuccess(): void
    {
        $registration = $this->createTestRegistration();
        $validationResult = new RequestAccessTokenValidationResult($registration);

        $boRequest = new BasicOutcomeRequest(
            'reqId',
            BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT,
            'sourcedId',
            0.42,
            'en'
        );

        $boProcessorResult = new BasicOutcomeServiceServerProcessorResult(
            true,
            'replace success description',
            0.42,
            'en'
        );

        $boResponse = new BasicOutcomeResponse(
            'respId',
            BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT,
            true,
            'reqId',
            BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT,
            'replace success description',
            0.42,
            'en'
        );

        $request = new ServerRequest(
            'POST',
            'http://platform.com/basic-outcome',
            [
                'Content-Type' => BasicOutcomeServiceInterface::CONTENT_TYPE_BASIC_OUTCOME
            ],
            $this->twig->render('request/replaceResultRequest.xml.twig', ['request' => $boRequest])
        );

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($validationResult);

        $this->processorMock
            ->expects($this->once())
            ->method('processReplaceResult')
            ->with($registration, 'sourcedId', 0.42, 'en')
            ->willReturn($boProcessorResult);

        $this->generatorMock
            ->expects($this->once())
            ->method('generate')
            ->willReturn('respId');

        $response = $this->server->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            $this->twig->render('response/replaceResultResponse.xml.twig', ['response' => $boResponse]),
            (string)$response->getBody()
        );

        $this->assertTrue($this->logger->hasLog('info', 'basic-outcome service success'));
    }

    public function testDeleteResultHandlingSuccess(): void
    {
        $registration = $this->createTestRegistration();
        $validationResult = new RequestAccessTokenValidationResult($registration);

        $boRequest = new BasicOutcomeRequest(
            'reqId',
            BasicOutcomeMessageInterface::TYPE_DELETE_RESULT,
            'sourcedId'
        );

        $boProcessorResult = new BasicOutcomeServiceServerProcessorResult(
            true,
            'delete success description'
        );

        $boResponse = new BasicOutcomeResponse(
            'respId',
            BasicOutcomeMessageInterface::TYPE_DELETE_RESULT,
            true,
            'reqId',
            BasicOutcomeMessageInterface::TYPE_DELETE_RESULT,
            'delete success description'
        );

        $request = new ServerRequest(
            'POST',
            'http://platform.com/basic-outcome',
            [
                'Content-Type' => BasicOutcomeServiceInterface::CONTENT_TYPE_BASIC_OUTCOME
            ],
            $this->twig->render('request/deleteResultRequest.xml.twig', ['request' => $boRequest])
        );

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($validationResult);

        $this->processorMock
            ->expects($this->once())
            ->method('processDeleteResult')
            ->with($registration, 'sourcedId')
            ->willReturn($boProcessorResult);

        $this->generatorMock
            ->expects($this->once())
            ->method('generate')
            ->willReturn('respId');

        $response = $this->server->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            $this->twig->render('response/deleteResultResponse.xml.twig', ['response' => $boResponse]),
            (string)$response->getBody()
        );

        $this->assertTrue($this->logger->hasLog('info', 'basic-outcome service success'));
    }

    public function testHandlingFailureOnInvalidBasicOutcomeRequestType(): void
    {
        $registration = $this->createTestRegistration();
        $validationResult = new RequestAccessTokenValidationResult($registration);

        $boRequest = new BasicOutcomeRequest(
            'reqId',
            'invalid',
            'sourcedId'
        );

        $request = new ServerRequest(
            'POST',
            'http://platform.com/basic-outcome',
            [
                'Content-Type' => BasicOutcomeServiceInterface::CONTENT_TYPE_BASIC_OUTCOME
            ],
            $this->twig->render('request/invalidRequest.xml.twig', ['request' => $boRequest])
        );

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($validationResult);

        $this->processorMock
            ->expects($this->never())
            ->method($this->anything());

        $this->generatorMock
            ->expects($this->never())
            ->method('generate');

        $response = $this->server->handle($request);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('Internal basic-outcome service error', (string)$response->getBody());

        $this->assertTrue($this->logger->hasLog('error', 'unsupported basic outcome type invalid'));
    }

    public function testHandlingFailureOnInvalidRequestAuthentication(): void
    {
        $registration = $this->createTestRegistration();
        $validationResult = new RequestAccessTokenValidationResult($registration, null, [], 'authentication error');

        $request = new ServerRequest(
            'POST',
            'http://platform.com/basic-outcome',
            [
                'Content-Type' => BasicOutcomeServiceInterface::CONTENT_TYPE_BASIC_OUTCOME
            ]
        );

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($validationResult);

        $this->processorMock
            ->expects($this->never())
            ->method($this->anything());

        $this->generatorMock
            ->expects($this->never())
            ->method('generate');

        $response = $this->server->handle($request);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('authentication error', (string)$response->getBody());

        $this->assertTrue($this->logger->hasLog('error', 'authentication error'));
    }

    public function testHandlingFailureOnInvalidRequestMethod(): void
    {
        $request = new ServerRequest(
            'GET',
            'http://platform.com/basic-outcome',
            [
                'Content-Type' => BasicOutcomeServiceInterface::CONTENT_TYPE_BASIC_OUTCOME
            ]
        );

        $this->validatorMock
            ->expects($this->never())
            ->method('validate');

        $this->processorMock
            ->expects($this->never())
            ->method($this->anything());

        $this->generatorMock
            ->expects($this->never())
            ->method('generate');

        $response = $this->server->handle($request);

        $this->assertEquals(405, $response->getStatusCode());
        $this->assertEquals('Not acceptable request method, accepts: [post]', (string)$response->getBody());

        $this->assertTrue($this->logger->hasLog('error', 'Not acceptable request method, accepts: [post]'));
    }

    public function testHandlingFailureOnInvalidRequestContentType(): void
    {
        $request = new ServerRequest(
            'POST',
            'http://platform.com/basic-outcome',
            [
                'Content-Type' => 'invalid'
            ]
        );

        $this->validatorMock
            ->expects($this->never())
            ->method('validate');

        $this->processorMock
            ->expects($this->never())
            ->method($this->anything());

        $this->generatorMock
            ->expects($this->never())
            ->method('generate');

        $response = $this->server->handle($request);

        $this->assertEquals(406, $response->getStatusCode());
        $this->assertEquals(
            'Not acceptable request content type, accepts: application/vnd.ims.lti.v1.outcome+xml',
            (string)$response->getBody()
        );

        $this->assertTrue(
            $this->logger->hasLog(
                'error',
                'Not acceptable request content type, accepts: application/vnd.ims.lti.v1.outcome+xml'
            )
        );
    }

    public function testHandlingFailureOnInvalidRequestXml(): void
    {
        $registration = $this->createTestRegistration();
        $validationResult = new RequestAccessTokenValidationResult($registration);

        $request = new ServerRequest(
            'POST',
            'http://platform.com/basic-outcome',
            [
                'Content-Type' => BasicOutcomeServiceInterface::CONTENT_TYPE_BASIC_OUTCOME
            ],
            'invalid'
        );

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($validationResult);

        $this->generatorMock
            ->expects($this->never())
            ->method('generate');

        $response = $this->server->handle($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            'Cannot deserialize basic outcome request: The current node list is empty.',
            (string)$response->getBody()
        );

        $this->assertTrue(
            $this->logger->hasLog(
                'error',
                'Cannot deserialize basic outcome request: The current node list is empty.'
            )
        );
    }
}
