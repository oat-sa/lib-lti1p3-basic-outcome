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
use OAT\Library\Lti1p3BasicOutcome\Factory\Request\BasicOutcomeRequestFactory;
use OAT\Library\Lti1p3BasicOutcome\Factory\Request\BasicOutcomeRequestFactoryInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\BasicOutcomeMessageInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\Response\BasicOutcomeResponseInterface;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Request\BasicOutcomeRequestSerializer;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Request\BasicOutcomeRequestSerializerInterface;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Response\BasicOutcomeResponseSerializer;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Response\BasicOutcomeResponseSerializerInterface;
use OAT\Library\Lti1p3BasicOutcome\Service\BasicOutcomeServiceInterface;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Service\Client\LtiServiceClient;
use OAT\Library\Lti1p3Core\Service\Client\LtiServiceClientInterface;
use Throwable;

/**
 * @see https://www.imsglobal.org/spec/lti-bo/v1p1#integration-with-lti-1-3
 */
class BasicOutcomeServiceClient implements BasicOutcomeServiceInterface
{
    /** @var LtiServiceClientInterface */
    private $client;

    /** @var BasicOutcomeRequestFactoryInterface */
    private $factory;

    /** @var BasicOutcomeRequestSerializerInterface */
    private $requestSerializer;

    /** @var BasicOutcomeResponseSerializerInterface */
    private $responseSerializer;

    public function __construct(
        ?LtiServiceClientInterface $client = null,
        ?BasicOutcomeRequestFactoryInterface $factory = null,
        ?BasicOutcomeRequestSerializerInterface $requestSerializer = null,
        ?BasicOutcomeResponseSerializerInterface $responseSerializer = null
    ) {
        $this->client = $client ?? new LtiServiceClient();
        $this->factory = $factory ?? new BasicOutcomeRequestFactory();
        $this->requestSerializer = $requestSerializer ?? new BasicOutcomeRequestSerializer();
        $this->responseSerializer = $responseSerializer ?? new BasicOutcomeResponseSerializer();
    }

    /**
     * @see https://www.imsglobal.org/spec/lti-bo/v1p1#readresult
     * @throws LtiExceptionInterface
     */
    public function readResultForPayload(
        RegistrationInterface $registration,
        LtiMessagePayloadInterface $payload
    ): BasicOutcomeResponseInterface {
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
                sprintf('Read result error for payload: %s', $exception->getMessage()),
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
    ): BasicOutcomeResponseInterface {
        try {
            $basicOutcomeRequest = $this->factory->create(
                BasicOutcomeMessageInterface::TYPE_READ_RESULT,
                $lisResultSourcedId
            );

            return $this->sendBasicOutcome(
                $registration,
                $lisOutcomeServiceUrl,
                $this->requestSerializer->serialize($basicOutcomeRequest)
            );

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
    ): BasicOutcomeResponseInterface {
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
    ): BasicOutcomeResponseInterface {
        try {
            if ($score < 0.0 || $score > 1.0) {
                throw new InvalidArgumentException('Score must be decimal numeric value in the range 0.0 - 1.0');
            }

            $basicOutcomeRequest = $this->factory->create(
                BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT,
                $lisResultSourcedId,
                $score,
                $language
            );

            return $this->sendBasicOutcome(
                $registration,
                $lisOutcomeServiceUrl,
                $this->requestSerializer->serialize($basicOutcomeRequest)
            );

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
    ): BasicOutcomeResponseInterface {
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
    ): BasicOutcomeResponseInterface {
        try {
            $basicOutcomeRequest = $this->factory->create(
                BasicOutcomeMessageInterface::TYPE_DELETE_RESULT,
                $lisResultSourcedId
            );

            return $this->sendBasicOutcome(
                $registration,
                $lisOutcomeServiceUrl,
                $this->requestSerializer->serialize($basicOutcomeRequest)
            );

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
    ): BasicOutcomeResponseInterface {
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

            return $this->responseSerializer->deserialize($response->getBody()->__toString());

        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot send basic outcome: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }
}
