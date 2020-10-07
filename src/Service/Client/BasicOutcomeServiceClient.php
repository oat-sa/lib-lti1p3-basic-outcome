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

namespace OAT\Library\Lti1p3BasicOutcome\Service\Client;

use InvalidArgumentException;
use OAT\Library\Lti1p3BasicOutcome\Generator\MessageIdentifierGenerator;
use OAT\Library\Lti1p3BasicOutcome\Generator\MessageIdentifierGeneratorInterface;
use OAT\Library\Lti1p3BasicOutcome\Result\BasicOutcomeResultFactory;
use OAT\Library\Lti1p3BasicOutcome\Result\BasicOutcomeResultFactoryInterface;
use OAT\Library\Lti1p3BasicOutcome\Result\BasicOutcomeResultInterface;
use OAT\Library\Lti1p3BasicOutcome\Service\BasicOutcomeServiceInterface;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Service\Client\ServiceClient;
use OAT\Library\Lti1p3Core\Service\Client\ServiceClientInterface;
use Throwable;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * @see https://www.imsglobal.org/spec/lti-bo/v1p1#integration-with-lti-1-3
 */
class BasicOutcomeServiceClient implements BasicOutcomeServiceInterface
{
    /** @var ServiceClientInterface */
    private $client;

    /** @var Environment */
    private $twig;

    /** @var MessageIdentifierGeneratorInterface */
    private $generator;

    /** @var BasicOutcomeResultFactoryInterface */
    private $factory;

    public function __construct(
        ServiceClientInterface $client = null,
        Environment $twig = null,
        MessageIdentifierGeneratorInterface $generator = null,
        BasicOutcomeResultFactoryInterface $factory = null
    ) {
        $this->client = $client ?? new ServiceClient();
        $this->twig = $twig ?? new Environment(new FilesystemLoader(__DIR__ . '/../../../templates'));
        $this->generator = $generator ?? new MessageIdentifierGenerator();
        $this->factory = $factory ?? new BasicOutcomeResultFactory();
    }

    /**
     * @see https://www.imsglobal.org/spec/lti-bo/v1p1#readresult
     * @throws LtiExceptionInterface
     */
    public function readResultFromPayload(
        RegistrationInterface $registration,
        LtiMessagePayloadInterface $payload
    ): BasicOutcomeResultInterface {
        try {
            if (null === $payload->getBasicOutcome()) {
                throw new InvalidArgumentException('Provided payload does not contain basic outcome claim');
            }

            return $this->readResult(
                $registration,
                $payload->getBasicOutcome()->getLisOutcomeServiceUrl(),
                $payload->getBasicOutcome()->getLisResultSourcedId()
            );

        } catch (LtiExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Read result error from payload: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @see https://www.imsglobal.org/spec/lti-bo/v1p1#readresult
     * @throws LtiExceptionInterface
     */
    public function readResult(
        RegistrationInterface $registration,
        string $lisOutcomeServiceUrl,
        string $lisResultSourcedId
    ): BasicOutcomeResultInterface {
        try {
            $xml = $this->twig->render(
                'basic-outcome/read-result.xml.twig',
                [
                    'messageIdentifier' => $this->generator->generate(),
                    'lisResultSourcedId' => $lisResultSourcedId
                ]
            );

            return $this->sendBasicOutcome($registration, $lisOutcomeServiceUrl, $xml);

        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Read result error: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @see https://www.imsglobal.org/spec/lti-bo/v1p1#replaceresult
     * @throws LtiExceptionInterface
     */
    public function replaceResultForPayload(
        RegistrationInterface $registration,
        LtiMessagePayloadInterface $payload,
        float $score,
        string $language = 'en'
    ): BasicOutcomeResultInterface {
        try {
            if (null === $payload->getBasicOutcome()) {
                throw new InvalidArgumentException('Provided payload does not contain basic outcome claim');
            }

            return $this->replaceResult(
                $registration,
                $payload->getBasicOutcome()->getLisOutcomeServiceUrl(),
                $payload->getBasicOutcome()->getLisResultSourcedId(),
                $score,
                $language
            );

        } catch (LtiExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Replace result error for payload: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @see https://www.imsglobal.org/spec/lti-bo/v1p1#replaceresult
     * @throws LtiExceptionInterface
     */
    public function replaceResult(
        RegistrationInterface $registration,
        string $lisOutcomeServiceUrl,
        string $lisResultSourcedId,
        float $score,
        string $language = 'en'
    ): BasicOutcomeResultInterface {
        try {
            if ($score < 0.0 || $score > 1.0) {
                throw new InvalidArgumentException('Score must be decimal numeric value in the range 0.0 - 1.0');
            }

            $xml = $this->twig->render(
                'basic-outcome/replace-result.xml.twig',
                [
                    'messageIdentifier' => $this->generator->generate(),
                    'lisResultSourcedId' => $lisResultSourcedId,
                    'score' => $score,
                    'language' => $language
                ]
            );

            return $this->sendBasicOutcome($registration, $lisOutcomeServiceUrl, $xml);

        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Replace result error: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @see https://www.imsglobal.org/spec/lti-bo/v1p1#deleteresult
     * @throws LtiExceptionInterface
     */
    public function deleteResultForPayload(
        RegistrationInterface $registration,
        LtiMessagePayloadInterface $payload
    ): BasicOutcomeResultInterface {
        try {
            if (null === $payload->getBasicOutcome()) {
                throw new InvalidArgumentException('Provided payload does not contain basic outcome claim');
            }

            return $this->deleteResult(
                $registration,
                $payload->getBasicOutcome()->getLisOutcomeServiceUrl(),
                $payload->getBasicOutcome()->getLisResultSourcedId()
            );

        } catch (LtiExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Delete result error for payload: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @see https://www.imsglobal.org/spec/lti-bo/v1p1#deleteresult
     * @throws LtiExceptionInterface
     */
    public function deleteResult(
        RegistrationInterface $registration,
        string $lisOutcomeServiceUrl,
        string $lisResultSourcedId
    ): BasicOutcomeResultInterface {
        try {
            $xml = $this->twig->render(
                'basic-outcome/delete-result.xml.twig',
                [
                    'messageIdentifier' => $this->generator->generate(),
                    'lisResultSourcedId' => $lisResultSourcedId
                ]
            );

            return $this->sendBasicOutcome($registration, $lisOutcomeServiceUrl, $xml);

        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Delete result error: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function sendBasicOutcome(
        RegistrationInterface $registration,
        string $lisOutcomeServiceUrl,
        string $xml
    ): BasicOutcomeResultInterface {
        try {
            $response = $this->client->request(
                $registration,
                'POST',
                $lisOutcomeServiceUrl,
                [
                    'headers' => [
                        'Content-Type' => static::CONTENT_TYPE_BASIC_OUTCOME,
                        'Content-Length' => strlen($xml),
                    ],
                    'body' => $xml
                ],
                [
                    static::AUTHORIZATION_SCOPE_BASIC_OUTCOME
                ]
            );

            return $this->factory->create($response->getBody()->__toString());

        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot send basic outcome: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }
}
