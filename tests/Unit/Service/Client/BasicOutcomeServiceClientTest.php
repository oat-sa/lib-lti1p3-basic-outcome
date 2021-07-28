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

namespace OAT\Library\Lti1p3BasicOutcome\Tests\Unit\Service\Client;

use Exception;
use OAT\Library\Lti1p3BasicOutcome\Factory\Request\BasicOutcomeRequestFactory;
use OAT\Library\Lti1p3BasicOutcome\Message\BasicOutcomeMessageInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\Request\BasicOutcomeRequest;
use OAT\Library\Lti1p3BasicOutcome\Message\Response\BasicOutcomeResponse;
use OAT\Library\Lti1p3BasicOutcome\Message\Response\BasicOutcomeResponseInterface;
use OAT\Library\Lti1p3BasicOutcome\Service\BasicOutcomeServiceInterface;
use OAT\Library\Lti1p3BasicOutcome\Service\Client\BasicOutcomeServiceClient;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\Payload\Builder\MessagePayloadBuilder;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\BasicOutcomeClaim;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayload;
use OAT\Library\Lti1p3Core\Message\Payload\MessagePayload;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Service\Client\LtiServiceClientInterface;
use OAT\Library\Lti1p3Core\Tests\Traits\DomainTestingTrait;
use OAT\Library\Lti1p3Core\Tests\Traits\NetworkTestingTrait;
use OAT\Library\Lti1p3Core\Util\Generator\IdGeneratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class BasicOutcomeServiceClientTest extends TestCase
{
    use DomainTestingTrait;
    use NetworkTestingTrait;

    /** @var Environment */
    private $twig;

    /** @var LtiServiceClientInterface|MockObject */
    private $clientMock;

    /** @var BasicOutcomeServiceClient */
    private $subject;

    protected function setUp(): void
    {
        $generatorMock = $this->createMock(IdGeneratorInterface::class);
        $generatorMock
            ->expects($this->any())
            ->method('generate')
            ->willReturn('reqId');

        $this->twig = new Environment(new FilesystemLoader(__DIR__ . '/../../../../templates'));
        $this->clientMock = $this->createMock(LtiServiceClientInterface::class);

        $this->subject = new BasicOutcomeServiceClient(
            $this->clientMock,
            new BasicOutcomeRequestFactory($generatorMock)
        );
    }

    public function testReadResultForPayloadSuccess(): void
    {
        $registration = $this->createTestRegistration();

        $payload = new LtiMessagePayload(
            (new MessagePayloadBuilder())
                ->withClaim(new BasicOutcomeClaim('sourcedId', 'http://platform.com/basic-outcome'))
                ->buildMessagePayload($registration->getToolKeyChain())
                ->getToken()
        );

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

        $this->prepareClientMockForSuccess(
            $registration,
            'http://platform.com/basic-outcome',
            $this->twig->render('request/readResultRequest.xml.twig', ['request' => $boRequest]),
            $this->twig->render('response/readResultResponse.xml.twig', ['response' => $boResponse])
        );

        $response = $this->subject->readResultForPayload($registration, $payload);

        $this->assertInstanceOf(BasicOutcomeResponseInterface::class, $response);

        $this->assertEquals('respId', $response->getIdentifier());
        $this->assertEquals(BasicOutcomeMessageInterface::TYPE_READ_RESULT, $response->getType());
        $this->assertTrue($response->isSuccess());
        $this->assertEquals('reqId', $response->getReferenceRequestIdentifier());
        $this->assertEquals(BasicOutcomeMessageInterface::TYPE_READ_RESULT, $response->getReferenceRequestType());
        $this->assertEquals('read description', $response->getDescription());
        $this->assertEquals(0.42, $response->getScore());
        $this->assertEquals('en', $response->getLanguage());
    }

    public function testReadResultForPayloadFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Read result for payload error: Provided payload does not contain basic outcome claim');

        $registration = $this->createTestRegistration();

        $payload = new LtiMessagePayload(
            (new MessagePayloadBuilder())
                ->buildMessagePayload($registration->getToolKeyChain())
                ->getToken()
        );

        $this->subject->readResultForPayload($registration, $payload);
    }

    public function testReadResultForPayloadClientFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Read result error: Cannot send basic outcome: custom error');

        $registration = $this->createTestRegistration();

        $payload = new LtiMessagePayload(
            (new MessagePayloadBuilder())
                ->withClaim(new BasicOutcomeClaim('sourcedId', 'http://platform.com/basic-outcome'))
                ->buildMessagePayload($registration->getToolKeyChain())
                ->getToken()
        );

        $this->prepareClientMockForFailure();

        $this->subject->readResultForPayload($registration, $payload);
    }

    public function testReadResultForClaimClientFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Read result for claim error: Read result error: Cannot send basic outcome: custom error');

        $registration = $this->createTestRegistration();

        $claim = new BasicOutcomeClaim('sourcedId', 'http://platform.com/basic-outcome');

        $this->prepareClientMockForFailure();

        $this->subject->readResultForClaim($registration, $claim);
    }

    public function testReadResultSuccess(): void
    {
        $registration = $this->createTestRegistration();

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

        $this->prepareClientMockForSuccess(
            $registration,
            'http://platform.com/basic-outcome',
            $this->twig->render('request/readResultRequest.xml.twig', ['request' => $boRequest]),
            $this->twig->render('response/readResultResponse.xml.twig', ['response' => $boResponse])
        );

        $response = $this->subject->readResult($registration, 'http://platform.com/basic-outcome', 'sourcedId');

        $this->assertInstanceOf(BasicOutcomeResponseInterface::class, $response);

        $this->assertEquals('respId', $response->getIdentifier());
        $this->assertEquals(BasicOutcomeMessageInterface::TYPE_READ_RESULT, $response->getType());
        $this->assertTrue($response->isSuccess());
        $this->assertEquals('reqId', $response->getReferenceRequestIdentifier());
        $this->assertEquals(BasicOutcomeMessageInterface::TYPE_READ_RESULT, $response->getReferenceRequestType());
        $this->assertEquals('read description', $response->getDescription());
        $this->assertEquals(0.42, $response->getScore());
        $this->assertEquals('en', $response->getLanguage());
    }

    public function testReadResultFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Read result error: Cannot send basic outcome: custom error');

        $this->prepareClientMockForFailure();

        $this->subject->readResult($this->createTestRegistration(), 'http://platform.com/basic-outcome', 'sourcedId');
    }

    public function testReplaceResultForPayloadSuccess(): void
    {
        $registration = $this->createTestRegistration();

        $payload = new LtiMessagePayload(
            (new MessagePayloadBuilder())
                ->withClaim(new BasicOutcomeClaim('sourcedId', 'http://platform.com/basic-outcome'))
                ->buildMessagePayload($registration->getToolKeyChain())
                ->getToken()
        );

        $boRequest = new BasicOutcomeRequest(
            'reqId',
            BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT,
            'sourcedId',
            0.42,
            'en'
        );

        $boResponse = new BasicOutcomeResponse(
            'respId',
            BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT,
            true,
            'reqId',
            BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT,
            'replace description',
            0.42,
            'en'
        );

        $this->prepareClientMockForSuccess(
            $registration,
            'http://platform.com/basic-outcome',
            $this->twig->render('request/replaceResultRequest.xml.twig', ['request' => $boRequest]),
            $this->twig->render('response/replaceResultResponse.xml.twig', ['response' => $boResponse])
        );

        $response = $this->subject->replaceResultForPayload($registration, $payload, 0.42, 'en');

        $this->assertInstanceOf(BasicOutcomeResponseInterface::class, $response);

        $this->assertEquals('respId', $response->getIdentifier());
        $this->assertEquals(BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT, $response->getType());
        $this->assertTrue($response->isSuccess());
        $this->assertEquals('reqId', $response->getReferenceRequestIdentifier());
        $this->assertEquals(BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT, $response->getReferenceRequestType());
        $this->assertEquals('replace description', $response->getDescription());
        $this->assertNull($response->getScore());
        $this->assertNull($response->getLanguage());
    }

    public function testReplaceResultForPayloadFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Replace result for payload error: Provided payload does not contain basic outcome claim');

        $registration = $this->createTestRegistration();

        $payload = new LtiMessagePayload(
            (new MessagePayloadBuilder())
                ->buildMessagePayload($registration->getToolKeyChain())
                ->getToken()
        );

        $this->subject->replaceResultForPayload($registration, $payload, 0.42, 'en');
    }

    public function testReplaceResultForPayloadClientFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Replace result error: Cannot send basic outcome: custom error');

        $registration = $this->createTestRegistration();

        $payload = new LtiMessagePayload(
            (new MessagePayloadBuilder())
                ->withClaim(new BasicOutcomeClaim('sourcedId', 'http://platform.com/basic-outcome'))
                ->buildMessagePayload($registration->getToolKeyChain())
                ->getToken()
        );

        $this->prepareClientMockForFailure();

        $this->subject->replaceResultForPayload($registration, $payload, 0.42, 'en');
    }

    public function testReplaceResultForClaimClientFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Replace result for claim error: Replace result error: Cannot send basic outcome: custom error');

        $registration = $this->createTestRegistration();

        $claim = new BasicOutcomeClaim('sourcedId', 'http://platform.com/basic-outcome');

        $this->prepareClientMockForFailure();

        $this->subject->replaceResultForClaim($registration, $claim, 0.42, 'en');
    }

    public function testReplaceResultSuccess(): void
    {
        $registration = $this->createTestRegistration();

        $boRequest = new BasicOutcomeRequest(
            'reqId',
            BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT,
            'sourcedId',
            0.42,
            'en'
        );

        $boResponse = new BasicOutcomeResponse(
            'respId',
            BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT,
            true,
            'reqId',
            BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT,
            'replace description'
        );

        $this->prepareClientMockForSuccess(
            $registration,
            'http://platform.com/basic-outcome',
            $this->twig->render('request/replaceResultRequest.xml.twig', ['request' => $boRequest]),
            $this->twig->render('response/replaceResultResponse.xml.twig', ['response' => $boResponse])
        );

        $response = $this->subject->replaceResult($registration, 'http://platform.com/basic-outcome', 'sourcedId', 0.42, 'en');

        $this->assertInstanceOf(BasicOutcomeResponseInterface::class, $response);

        $this->assertEquals('respId', $response->getIdentifier());
        $this->assertEquals(BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT, $response->getType());
        $this->assertTrue($response->isSuccess());
        $this->assertEquals('reqId', $response->getReferenceRequestIdentifier());
        $this->assertEquals(BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT, $response->getReferenceRequestType());
        $this->assertEquals('replace description', $response->getDescription());
        $this->assertNull($response->getScore());
        $this->assertNull($response->getLanguage());
    }

    public function testReplaceResultFailureWithInvalidScore(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Replace result error: Score must be decimal numeric value in the range 0.0 - 1.0');

        $this->subject->replaceResult($this->createTestRegistration(), 'http://platform.com/basic-outcome', 'sourcedId', 42, 'en');
    }

    public function testReplaceResultFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Replace result error: Cannot send basic outcome: custom error');

        $this->prepareClientMockForFailure();

        $this->subject->replaceResult($this->createTestRegistration(), 'http://platform.com/basic-outcome', 'sourcedId', 0.42, 'en');
    }

    public function testDeleteResultForPayloadSuccess(): void
    {
        $registration = $this->createTestRegistration();

        $payload = new LtiMessagePayload(
            (new MessagePayloadBuilder())
                ->withClaim(new BasicOutcomeClaim('sourcedId', 'http://platform.com/basic-outcome'))
                ->buildMessagePayload($registration->getToolKeyChain())
                ->getToken()
        );

        $boRequest = new BasicOutcomeRequest(
            'reqId',
            BasicOutcomeMessageInterface::TYPE_DELETE_RESULT,
            'sourcedId'
        );

        $boResponse = new BasicOutcomeResponse(
            'respId',
            BasicOutcomeMessageInterface::TYPE_DELETE_RESULT,
            true,
            'reqId',
            BasicOutcomeMessageInterface::TYPE_DELETE_RESULT,
            'delete description'
        );

        $this->prepareClientMockForSuccess(
            $registration,
            'http://platform.com/basic-outcome',
            $this->twig->render('request/deleteResultRequest.xml.twig', ['request' => $boRequest]),
            $this->twig->render('response/deleteResultResponse.xml.twig', ['response' => $boResponse])
        );

        $response = $this->subject->deleteResultForPayload($registration, $payload);

        $this->assertInstanceOf(BasicOutcomeResponseInterface::class, $response);

        $this->assertEquals('respId', $response->getIdentifier());
        $this->assertEquals(BasicOutcomeMessageInterface::TYPE_DELETE_RESULT, $response->getType());
        $this->assertTrue($response->isSuccess());
        $this->assertEquals('reqId', $response->getReferenceRequestIdentifier());
        $this->assertEquals(BasicOutcomeMessageInterface::TYPE_DELETE_RESULT, $response->getReferenceRequestType());
        $this->assertEquals('delete description', $response->getDescription());
        $this->assertNull($response->getScore());
        $this->assertNull($response->getLanguage());
    }

    public function testDeleteResultForPayloadFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Delete result for payload error: Provided payload does not contain basic outcome claim');

        $registration = $this->createTestRegistration();

        $payload = new LtiMessagePayload(
            (new MessagePayloadBuilder())
                ->buildMessagePayload($registration->getToolKeyChain())
                ->getToken()
        );

        $this->subject->deleteResultForPayload($registration, $payload);
    }

    public function testDeleteResultForPayloadClientFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Delete result error: Cannot send basic outcome: custom error');

        $registration = $this->createTestRegistration();

        $payload = new LtiMessagePayload(
            (new MessagePayloadBuilder())
                ->withClaim(new BasicOutcomeClaim('sourcedId', 'http://platform.com/basic-outcome'))
                ->buildMessagePayload($registration->getToolKeyChain())
                ->getToken()
        );

        $this->prepareClientMockForFailure();

        $this->subject->deleteResultForPayload($registration, $payload);
    }

    public function testDeleteResultForClaimClientFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Delete result for claim error: Delete result error: Cannot send basic outcome: custom error');

        $registration = $this->createTestRegistration();

        $claim = new BasicOutcomeClaim('sourcedId', 'http://platform.com/basic-outcome');

        $this->prepareClientMockForFailure();

        $this->subject->deleteResultForClaim($registration, $claim);
    }

    public function testDeleteResultSuccess(): void
    {
        $registration = $this->createTestRegistration();

        $boRequest = new BasicOutcomeRequest(
            'reqId',
            BasicOutcomeMessageInterface::TYPE_DELETE_RESULT,
            'sourcedId'
        );

        $boResponse = new BasicOutcomeResponse(
            'respId',
            BasicOutcomeMessageInterface::TYPE_DELETE_RESULT,
            true,
            'reqId',
            BasicOutcomeMessageInterface::TYPE_DELETE_RESULT,
            'delete description'
        );

        $this->prepareClientMockForSuccess(
            $registration,
            'http://platform.com/basic-outcome',
            $this->twig->render('request/deleteResultRequest.xml.twig', ['request' => $boRequest]),
            $this->twig->render('response/deleteResultResponse.xml.twig', ['response' => $boResponse])
        );

        $response = $this->subject->deleteResult($registration, 'http://platform.com/basic-outcome', 'sourcedId');

        $this->assertInstanceOf(BasicOutcomeResponseInterface::class, $response);

        $this->assertEquals('respId', $response->getIdentifier());
        $this->assertEquals(BasicOutcomeMessageInterface::TYPE_DELETE_RESULT, $response->getType());
        $this->assertTrue($response->isSuccess());
        $this->assertEquals('reqId', $response->getReferenceRequestIdentifier());
        $this->assertEquals(BasicOutcomeMessageInterface::TYPE_DELETE_RESULT, $response->getReferenceRequestType());
        $this->assertEquals('delete description', $response->getDescription());
        $this->assertNull($response->getScore());
        $this->assertNull($response->getLanguage());
    }

    public function testDeleteResultFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Delete result error: Cannot send basic outcome: custom error');

        $this->prepareClientMockForFailure();

        $this->subject->deleteResult($this->createTestRegistration(), 'http://platform.com/basic-outcome', 'sourcedId');
    }

    private function prepareClientMockForSuccess(
        RegistrationInterface $registration,
        string $requestUrl,
        string $requestPayload,
        string $responsePayload
    ): void {
        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                $registration,
                'POST',
                $requestUrl,
                [
                    'headers' => [
                        'Content-Type' => BasicOutcomeServiceInterface::CONTENT_TYPE_BASIC_OUTCOME,
                        'Content-Length' => strlen($requestPayload),
                    ],
                    'body' => $requestPayload
                ],
                [
                    BasicOutcomeServiceInterface::AUTHORIZATION_SCOPE_BASIC_OUTCOME
                ]
            )
            ->willReturn($this->createResponse($responsePayload));
    }

    private function prepareClientMockForFailure(): void
    {
        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new Exception('custom error'));
    }
}
