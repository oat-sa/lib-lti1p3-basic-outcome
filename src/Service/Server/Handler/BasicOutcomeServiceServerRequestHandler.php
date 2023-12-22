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

namespace OAT\Library\Lti1p3BasicOutcome\Service\Server\Handler;

use InvalidArgumentException;
use Nyholm\Psr7\Response;
use OAT\Library\Lti1p3BasicOutcome\Factory\Response\BasicOutcomeResponseFactory;
use OAT\Library\Lti1p3BasicOutcome\Factory\Response\BasicOutcomeResponseFactoryInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\BasicOutcomeMessageInterface;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Request\BasicOutcomeRequestSerializer;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Request\BasicOutcomeRequestSerializerInterface;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Response\BasicOutcomeResponseSerializer;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Response\BasicOutcomeResponseSerializerInterface;
use OAT\Library\Lti1p3BasicOutcome\Service\BasicOutcomeServiceInterface;
use OAT\Library\Lti1p3BasicOutcome\Service\Server\Processor\BasicOutcomeServiceServerProcessorInterface;
use OAT\Library\Lti1p3Core\Security\OAuth2\Validator\Result\RequestAccessTokenValidationResultInterface;
use OAT\Library\Lti1p3Core\Service\Server\Handler\LtiServiceServerRequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

class BasicOutcomeServiceServerRequestHandler implements LtiServiceServerRequestHandlerInterface, BasicOutcomeServiceInterface
{
    /** @var BasicOutcomeServiceServerProcessorInterface */
    private $processor;

    /** @var BasicOutcomeRequestSerializerInterface */
    private $basicOutcomeRequestSerializer;

    /** @var BasicOutcomeResponseSerializerInterface */
    private $basicOutcomeResponseSerializer;

    /** @var BasicOutcomeResponseFactoryInterface */
    private $basicOutcomeResponseFactory;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        BasicOutcomeServiceServerProcessorInterface $processor,
        ?BasicOutcomeRequestSerializerInterface $basicOutcomeRequestSerializer = null,
        ?BasicOutcomeResponseSerializerInterface $basicOutcomeResponseSerializer = null,
        ?BasicOutcomeResponseFactoryInterface $basicOutcomeResponseFactory = null,
        ?LoggerInterface $logger = null
    ) {
        $this->processor = $processor;
        $this->basicOutcomeRequestSerializer = $basicOutcomeRequestSerializer ?? new BasicOutcomeRequestSerializer();
        $this->basicOutcomeResponseSerializer = $basicOutcomeResponseSerializer ?? new BasicOutcomeResponseSerializer();
        $this->basicOutcomeResponseFactory = $basicOutcomeResponseFactory ?? new BasicOutcomeResponseFactory();
        $this->logger = $logger ?? new NullLogger();
    }

    public function getServiceName(): string
    {
        return static::NAME;
    }

    public function getAllowedContentType(): ?string
    {
        return static::CONTENT_TYPE_BASIC_OUTCOME;
    }

    public function getAllowedMethods(): array
    {
        return [
            'POST',
        ];
    }

    public function getAllowedScopes(): array
    {
        return [
            static::AUTHORIZATION_SCOPE_BASIC_OUTCOME,
        ];
    }

    public function handleValidatedServiceRequest(
        RequestAccessTokenValidationResultInterface $validationResult,
        ServerRequestInterface $request,
        array $options = []
    ): ResponseInterface {
        $registration = $validationResult->getRegistration();

        try {
            $basicOutcomeRequest = $this->basicOutcomeRequestSerializer->deserialize((string)$request->getBody());
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return new Response(400, [], $exception->getMessage());
        }

        switch ($basicOutcomeRequest->getType()) {
            case BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT:
                $result = $this->processor->processReplaceResult(
                    $registration,
                    $basicOutcomeRequest->getSourcedId(),
                    $basicOutcomeRequest->getScore(),
                    $basicOutcomeRequest->getLanguage()
                );
                break;
            case BasicOutcomeMessageInterface::TYPE_READ_RESULT:
                $result = $this->processor->processReadResult(
                    $registration,
                    $basicOutcomeRequest->getSourcedId()
                );
                break;
            case BasicOutcomeMessageInterface::TYPE_DELETE_RESULT:
                $result = $this->processor->processDeleteResult(
                    $registration,
                    $basicOutcomeRequest->getSourcedId()
                );
                break;
            default:
                throw new InvalidArgumentException(
                    sprintf('unsupported basic outcome type %s', $basicOutcomeRequest->getType())
                );
        }

        $basicOutcomeResponse =  $this->basicOutcomeResponseFactory->create(
            $basicOutcomeRequest->getType(),
            $result->isSuccess(),
            $basicOutcomeRequest->getIdentifier(),
            $basicOutcomeRequest->getType(),
            $result->getMessage(),
            $result->getScore(),
            $result->getLanguage()
        );

        $responseBody = $this->basicOutcomeResponseSerializer->serialize($basicOutcomeResponse);

        return new Response(
            200,
            [
                'Content-Type' => static::CONTENT_TYPE_BASIC_OUTCOME,
                'Content-Length' => strlen($responseBody),
            ],
            $responseBody
        );
    }
}
