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
use OAT\Library\Lti1p3BasicOutcome\Factory\OutcomeMessageFactory;
use OAT\Library\Lti1p3BasicOutcome\Message\OutcomeResponse;
use OAT\Library\Lti1p3BasicOutcome\Serializer\OutcomeMessageSerializer;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Message\Claim\BasicOutcomeClaim;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Service\Client\ServiceClientInterface;
use Throwable;

/**
 * @see https://www.imsglobal.org/spec/lti-bo/v1p1#integration-with-lti-1-3
 */
class OutcomeServiceClient
{
    public const AUTHORIZATION_SCOPE_BASIC_OUTCOME = 'https://purl.imsglobal.org/spec/lti-bo/scope/basicoutcome';
    public const CONTENT_TYPE_BASIC_OUTCOME = 'application/vnd.ims.lti.v1.outcome+xml';

    /** @var ServiceClientInterface */
    private $client;

    /** @var OutcomeMessageFactory */
    private $messageFactory;

    /** @var OutcomeMessageSerializer */
    private $messageSerializer;

    public function __construct(
        ServiceClientInterface $client,
        OutcomeMessageFactory $messageFactory = null,
        OutcomeMessageSerializer $messageSerializer = null
    ) {
        $this->client = $client;
        $this->messageFactory = $messageFactory ?? new OutcomeMessageFactory();
        $this->messageSerializer = $messageSerializer ?? new OutcomeMessageSerializer();
    }

    /**
     * @see https://www.imsglobal.org/spec/lti-bo/v1p1#readresult
     * @throws LtiException
     */
    public function readResult(
        RegistrationInterface $registration,
        BasicOutcomeClaim $basicOutcomeClaim
    ): OutcomeResponse {
        try {
            $request = $this->messageFactory->createReadResultRequest($basicOutcomeClaim->getLisResultSourcedId());

            return $this->sendOutcome(
                $registration,
                $basicOutcomeClaim,
                $this->messageSerializer->serializeRequest($request)
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
     * @se https://www.imsglobal.org/spec/lti-bo/v1p1#replaceresult
     * @throws LtiException
     */
    public function replaceResult(
        RegistrationInterface $registration,
        BasicOutcomeClaim $basicOutcomeClaim,
        float $score,
        string $language = 'en'
    ): OutcomeResponse {
        try {
            if ($score < 0.0 || $score > 1.0) {
                throw new InvalidArgumentException('Score must be decimal numeric value in the range 0.0 - 1.0');
            }

            $request = $this->messageFactory->createReplaceResultRequest(
                $basicOutcomeClaim->getLisResultSourcedId(),
                $score,
                $language
            );

            return $this->sendOutcome(
                $registration,
                $basicOutcomeClaim,
                $this->messageSerializer->serializeRequest($request)
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
     * @se https://www.imsglobal.org/spec/lti-bo/v1p1#deleteresult
     * @throws LtiException
     */
    public function deleteResult(
        RegistrationInterface $registration,
        BasicOutcomeClaim $basicOutcomeClaim
    ): OutcomeResponse {
        try {
            $request = $this->messageFactory->createDeleteResultRequest($basicOutcomeClaim->getLisResultSourcedId());

            return $this->sendOutcome(
                $registration,
                $basicOutcomeClaim,
                $this->messageSerializer->serializeRequest($request)
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
     * @throws LtiException
     */
    public function sendOutcome(
        RegistrationInterface $registration,
        BasicOutcomeClaim $basicOutcomeClaim,
        string $outcomeXML
    ): OutcomeResponse {
        try {
            $response = $this->client->request(
                $registration,
                'POST',
                $basicOutcomeClaim->getLisOutcomeServiceUrl(),
                [
                    'headers' => [
                        'Content-Type' => static::CONTENT_TYPE_BASIC_OUTCOME,
                        'Content-Length' => strlen($outcomeXML),
                    ],
                    'body' => $outcomeXML
                ],
                [
                    static::AUTHORIZATION_SCOPE_BASIC_OUTCOME
                ]
            );

            return $this->messageSerializer->deserializeResponse($response->getBody()->__toString());

        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot send outcome: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }
}
