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

use Http\Message\ResponseFactory;
use InvalidArgumentException;
use Nyholm\Psr7\Factory\HttplugFactory;
use OAT\Library\Lti1p3BasicOutcome\Factory\Response\BasicOutcomeResponseFactory;
use OAT\Library\Lti1p3BasicOutcome\Factory\Response\BasicOutcomeResponseFactoryInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\BasicOutcomeMessageInterface;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Request\BasicOutcomeRequestSerializer;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Request\BasicOutcomeRequestSerializerInterface;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Response\BasicOutcomeResponseSerializer;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Response\BasicOutcomeResponseSerializerInterface;
use OAT\Library\Lti1p3BasicOutcome\Service\BasicOutcomeServiceInterface;
use OAT\Library\Lti1p3BasicOutcome\Service\Server\Processor\BasicOutcomeServiceServerProcessorInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Service\Server\Handler\LtiServiceServerRequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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

    /** @var ResponseFactory */
    private $httpResponseFactory;

    public function __construct(
        BasicOutcomeServiceServerProcessorInterface $processor,
        ?BasicOutcomeRequestSerializerInterface $basicOutcomeRequestSerializer = null,
        ?BasicOutcomeResponseSerializerInterface $basicOutcomeResponseSerializer = null,
        ?BasicOutcomeResponseFactoryInterface $basicOutcomeResponseFactory = null,
        ?ResponseFactory $httpResponseFactory = null
    ) {
        $this->processor = $processor;
        $this->basicOutcomeRequestSerializer = $basicOutcomeRequestSerializer ?? new BasicOutcomeRequestSerializer();
        $this->basicOutcomeResponseSerializer = $basicOutcomeResponseSerializer ?? new BasicOutcomeResponseSerializer();
        $this->basicOutcomeResponseFactory = $basicOutcomeResponseFactory ?? new BasicOutcomeResponseFactory();
        $this->httpResponseFactory = $httpResponseFactory ?? new HttplugFactory();
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

    public function handleServiceRequest(
        RegistrationInterface $registration,
        ServerRequestInterface $request
    ): ResponseInterface {
        $basicOutcomeRequest = $this->basicOutcomeRequestSerializer->deserialize((string)$request->getBody());

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

        return $this->httpResponseFactory->createResponse(
            200,
            null,
            [
                'Content-Type' => static::CONTENT_TYPE_BASIC_OUTCOME,
                'Content-Length' => strlen($responseBody),
            ],
            $responseBody
        );
    }
}
