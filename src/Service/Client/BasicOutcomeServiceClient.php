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
use OAT\Library\Lti1p3BasicOutcome\Result\BasicOutcomeResult;
use OAT\Library\Lti1p3BasicOutcome\Result\BasicOutcomeResultInterface;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Message\Claim\BasicOutcomeClaim;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Service\Client\ServiceClient;
use OAT\Library\Lti1p3Core\Service\Client\ServiceClientInterface;
use Throwable;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * @see https://www.imsglobal.org/spec/lti-bo/v1p1#integration-with-lti-1-3
 */
class BasicOutcomeServiceClient
{
    public const AUTHORIZATION_SCOPE_BASIC_OUTCOME = 'https://purl.imsglobal.org/spec/lti-bo/scope/basicoutcome';
    public const CONTENT_TYPE_BASIC_OUTCOME = 'application/vnd.ims.lti.v1.outcome+xml';

    /** @var ServiceClientInterface */
    private $client;

    /** @var Environment */
    private $twig;

    /** @var MessageIdentifierGeneratorInterface */
    private $messageIdentifierGenerator;

    public function __construct(
        ServiceClientInterface $client = null,
        Environment $twig = null,
        MessageIdentifierGeneratorInterface $messageIdentifierGenerator = null
    ) {
        $this->client = $client ?? new ServiceClient();
        $this->twig = $twig ?? new Environment(new FilesystemLoader(__DIR__ . '/../../../templates'));
        $this->messageIdentifierGenerator = $messageIdentifierGenerator ?? new MessageIdentifierGenerator();
    }

    /**
     * @see https://www.imsglobal.org/spec/lti-bo/v1p1#readresult
     * @throws LtiException
     */
    public function readResult(
        RegistrationInterface $registration,
        BasicOutcomeClaim $basicOutcomeClaim
    ): BasicOutcomeResultInterface {
        try {
            $xml = $this->twig->render('basic-outcome/read-result.xml.twig', [
                'messageIdentifier' => $this->messageIdentifierGenerator->generate(),
                'lisResultSourcedId' => $basicOutcomeClaim->getLisResultSourcedId()
            ]);

            return $this->sendBasicOutcome($registration, $basicOutcomeClaim, $xml);

        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot read result: %s', $exception->getMessage()),
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
    ): BasicOutcomeResultInterface {
        try {
            if ($score < 0.0 || $score > 1.0) {
                throw new InvalidArgumentException('Score must be decimal numeric value in the range 0.0 - 1.0');
            }

            $xml = $this->twig->render('basic-outcome/replace-result.xml.twig', [
                'messageIdentifier' => $this->messageIdentifierGenerator->generate(),
                'lisResultSourcedId' => $basicOutcomeClaim->getLisResultSourcedId(),
                'score' => $score,
                'language' => $language
            ]);

            return $this->sendBasicOutcome($registration, $basicOutcomeClaim, $xml);

        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot replace result: %s', $exception->getMessage()),
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
    ): BasicOutcomeResultInterface {
        try {
            $xml = $this->twig->render('basic-outcome/delete-result.xml.twig', [
                'messageIdentifier' => $this->messageIdentifierGenerator->generate(),
                'lisResultSourcedId' => $basicOutcomeClaim->getLisResultSourcedId()
            ]);

            return $this->sendBasicOutcome($registration, $basicOutcomeClaim, $xml);

        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot delete result: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiException
     */
    public function sendBasicOutcome(
        RegistrationInterface $registration,
        BasicOutcomeClaim $basicOutcomeClaim,
        string $basicOutcomeXML
    ): BasicOutcomeResultInterface {
        try {
            $response = $this->client->request(
                $registration,
                'POST',
                $basicOutcomeClaim->getLisOutcomeServiceUrl(),
                [
                    'headers' => [
                        'Content-Type' => static::CONTENT_TYPE_BASIC_OUTCOME,
                        'Content-Length' => strlen($basicOutcomeXML),
                    ],
                    'body' => $basicOutcomeXML
                ],
                [
                    static::AUTHORIZATION_SCOPE_BASIC_OUTCOME
                ]
            );

            return new BasicOutcomeResult($response->getBody()->__toString());

        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot send basic outcome: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }
}
