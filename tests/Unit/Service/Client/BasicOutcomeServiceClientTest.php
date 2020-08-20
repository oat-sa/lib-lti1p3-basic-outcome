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

use OAT\Library\Lti1p3BasicOutcome\Factory\BasicOutcomeResultCrawlerFactory;
use OAT\Library\Lti1p3BasicOutcome\Generator\MessageIdentifierGeneratorInterface;
use OAT\Library\Lti1p3BasicOutcome\Result\BasicOutcomeResultInterface;
use OAT\Library\Lti1p3BasicOutcome\Service\Client\BasicOutcomeServiceClient;
use OAT\Library\Lti1p3BasicOutcome\Tests\Traits\TwigTestingTrait;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\Claim\BasicOutcomeClaim;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Service\Client\ServiceClientInterface;
use OAT\Library\Lti1p3Core\Tests\Traits\DomainTestingTrait;
use OAT\Library\Lti1p3Core\Tests\Traits\NetworkTestingTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Exception;

class MessageIdentifierGeneratorTest extends TestCase
{
    use DomainTestingTrait;
    use NetworkTestingTrait;
    use TwigTestingTrait;

    private const TEST_SOURCED_ID = '123';
    private const TEST_MESSAGE_ID = '456';
    private const TEST_OUTCOME_URL = 'http://example.com/outcome';

    /** @var ServiceClientInterface|MockObject */
    private $serviceClientMock;

    /** @var RegistrationInterface */
    private $registration;

    /** @var BasicOutcomeClaim */
    private $basicOutcomeClaim;

    /** @var BasicOutcomeResultCrawlerFactory */
    private $crawlerFactory;

    /** @var BasicOutcomeServiceClient */
    private $subject;

    protected function setUp(): void
    {
        $this->setUpTwig();

        $this->registration = $this->createTestRegistration();
        $this->basicOutcomeClaim = new BasicOutcomeClaim(self::TEST_SOURCED_ID, self::TEST_OUTCOME_URL);
        $this->crawlerFactory = new BasicOutcomeResultCrawlerFactory();

        $messageIdentifierGeneratorMock = $this->createMock(MessageIdentifierGeneratorInterface::class);
        $messageIdentifierGeneratorMock
            ->expects($this->any())
            ->method('generate')
            ->willReturn(static::TEST_MESSAGE_ID);
        $this->serviceClientMock = $this->createMock(ServiceClientInterface::class);

        $this->subject = new BasicOutcomeServiceClient(
            $this->serviceClientMock,
            $this->twig,
            $messageIdentifierGeneratorMock
        );
    }

    public function testReadResultSuccess(): void
    {
        $bodyContent = $this->twig->render('basic-outcome/read-result.xml.twig', [
            'messageIdentifier' => static::TEST_MESSAGE_ID,
            'lisResultSourcedId' => static::TEST_SOURCED_ID
        ]);

        $responseContent = $this->twig->render('read-result-response.xml.twig', [
            'refMessageIdentifier' => static::TEST_MESSAGE_ID,
            'status' => 'success'
        ]);

        $this->prepareServiceClientForSuccess($bodyContent, $responseContent);

        $result = $this->subject->readResult($this->registration, $this->basicOutcomeClaim);

        $this->assertInstanceOf(BasicOutcomeResultInterface::class, $result);
        $this->assertTrue($result->isSuccess());

        $crawler = $this->crawlerFactory->create($result);
        $this->assertEquals(
            '789',
            $crawler->filterXPath('//imsx_POXEnvelopeResponse/imsx_POXHeader/imsx_POXResponseHeaderInfo/imsx_messageIdentifier')->text()
        );
    }

    public function testReadResultFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Read result error: Cannot send basic outcome: error');

        $bodyContent = $this->twig->render('basic-outcome/read-result.xml.twig', [
            'messageIdentifier' => static::TEST_MESSAGE_ID,
            'lisResultSourcedId' => static::TEST_SOURCED_ID
        ]);

        $this->prepareServiceClientForError($bodyContent);

        $this->subject->readResult($this->registration, $this->basicOutcomeClaim);
    }

    public function testReplaceResultSuccess(): void
    {
        $score = 0.5;
        $language = 'fr';

        $bodyContent = $this->twig->render('basic-outcome/replace-result.xml.twig', [
            'messageIdentifier' => static::TEST_MESSAGE_ID,
            'lisResultSourcedId' => static::TEST_SOURCED_ID,
            'score' => $score,
            'language' => $language
        ]);

        $responseContent = $this->twig->render('replace-result-response.xml.twig', [
            'refMessageIdentifier' => static::TEST_MESSAGE_ID,
            'status' => 'success',
            'score' => $score,
            'language' => $language
        ]);

        $this->prepareServiceClientForSuccess($bodyContent, $responseContent);

        $result = $this->subject->replaceResult($this->registration, $this->basicOutcomeClaim, $score, $language);

        $this->assertInstanceOf(BasicOutcomeResultInterface::class, $result);
        $this->assertTrue($result->isSuccess());

        $crawler = $this->crawlerFactory->create($result);
        $this->assertEquals(
            '789',
            $crawler->filterXPath('//imsx_POXEnvelopeResponse/imsx_POXHeader/imsx_POXResponseHeaderInfo/imsx_messageIdentifier')->text()
        );
    }

    public function testReplaceResultFailureWithInvalidScore(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Score must be decimal numeric value in the range 0.0 - 1.0');

        $this->subject->replaceResult($this->registration, $this->basicOutcomeClaim, 999);
    }

    public function testReplaceResultFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Replace result error: Cannot send basic outcome: error');

        $score = 0.5;
        $language = 'fr';

        $bodyContent = $this->twig->render('basic-outcome/replace-result.xml.twig', [
            'messageIdentifier' => static::TEST_MESSAGE_ID,
            'lisResultSourcedId' => static::TEST_SOURCED_ID,
            'score' => $score,
            'language' => $language
        ]);

        $this->prepareServiceClientForError($bodyContent);

        $this->subject->replaceResult($this->registration, $this->basicOutcomeClaim, $score, $language);
    }

    public function testDeleteResultSuccess(): void
    {
        $bodyContent = $this->twig->render('basic-outcome/delete-result.xml.twig', [
            'messageIdentifier' => static::TEST_MESSAGE_ID,
            'lisResultSourcedId' => static::TEST_SOURCED_ID
        ]);

        $responseContent = $this->twig->render('delete-result-response.xml.twig', [
            'refMessageIdentifier' => static::TEST_MESSAGE_ID,
            'status' => 'success'
        ]);

        $this->prepareServiceClientForSuccess($bodyContent, $responseContent);

        $result = $this->subject->deleteResult($this->registration, $this->basicOutcomeClaim);

        $this->assertInstanceOf(BasicOutcomeResultInterface::class, $result);
        $this->assertTrue($result->isSuccess());

        $crawler = $this->crawlerFactory->create($result);
        $this->assertEquals(
            '789',
            $crawler->filterXPath('//imsx_POXEnvelopeResponse/imsx_POXHeader/imsx_POXResponseHeaderInfo/imsx_messageIdentifier')->text()
        );
    }

    public function testDeleteResultFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Delete result error: Cannot send basic outcome: error');

        $bodyContent = $this->twig->render('basic-outcome/delete-result.xml.twig', [
            'messageIdentifier' => static::TEST_MESSAGE_ID,
            'lisResultSourcedId' => static::TEST_SOURCED_ID,
        ]);

        $this->prepareServiceClientForError($bodyContent);

        $this->subject->deleteResult($this->registration, $this->basicOutcomeClaim);
    }

    private function prepareServiceClientForSuccess(string $bodyContent, string $responseContent): void
    {
        $this->serviceClientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->registration,
                'POST',
                $this->basicOutcomeClaim->getLisOutcomeServiceUrl(),
                $this->prepareServiceClientOptions($bodyContent),
                [
                    BasicOutcomeServiceClient::AUTHORIZATION_SCOPE_BASIC_OUTCOME
                ]
            )
            ->willReturn($this->createResponse($responseContent));
    }

    private function prepareServiceClientForError(string $bodyContent): void
    {
        $this->serviceClientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->registration,
                'POST',
                $this->basicOutcomeClaim->getLisOutcomeServiceUrl(),
                $this->prepareServiceClientOptions($bodyContent),
                [
                    BasicOutcomeServiceClient::AUTHORIZATION_SCOPE_BASIC_OUTCOME
                ]
            )
            ->willThrowException(new Exception('error'));
    }

    private function prepareServiceClientOptions(string $bodyContent): array
    {
        return [
            'headers' => [
                'Content-Type' => BasicOutcomeServiceClient::CONTENT_TYPE_BASIC_OUTCOME,
                'Content-Length' => strlen($bodyContent)
            ],
            'body' => $bodyContent
        ];
    }
}
