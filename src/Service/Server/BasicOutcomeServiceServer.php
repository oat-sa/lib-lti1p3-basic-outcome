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

namespace OAT\Library\Lti1p3BasicOutcome\Service\Server;

use Http\Message\ResponseFactory;
use Nyholm\Psr7\Factory\HttplugFactory;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Request\BasicOutcomeRequestSerializer;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Request\BasicOutcomeRequestSerializerInterface;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Response\BasicOutcomeResponseSerializer;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Response\BasicOutcomeResponseSerializerInterface;
use OAT\Library\Lti1p3BasicOutcome\Service\BasicOutcomeServiceInterface;
use OAT\Library\Lti1p3BasicOutcome\Service\Server\Handler\BasicOutcomeServiceServerHandler;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

/**
 * @see https://www.imsglobal.org/spec/lti-bo/v1p1#integration-with-lti-1-3
 */
class BasicOutcomeServiceServer implements BasicOutcomeServiceInterface, RequestHandlerInterface
{
    /** @var AccessTokenRequestValidator */
    private $validator;

    /** @var BasicOutcomeServiceServerHandler */
    private $handler;

    /** @var ResponseFactory */
    private $httpResponseFactory;

    /** @var BasicOutcomeRequestSerializerInterface */
    private $basicOutcomeRequestSerializer;

    /** @var BasicOutcomeResponseSerializerInterface */
    private $basicOutcomeResponseSerializer;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        AccessTokenRequestValidator $validator,
        BasicOutcomeServiceServerHandler $handler,
        ResponseFactory $httpResponseFactory = null,
        BasicOutcomeRequestSerializerInterface $basicOutcomeRequestSerializer = null,
        BasicOutcomeResponseSerializerInterface $basicOutcomeResponseSerializer = null,
        LoggerInterface $logger = null
    ) {
        $this->validator = $validator;
        $this->handler = $handler;
        $this->httpResponseFactory = $httpResponseFactory ?? new HttplugFactory();
        $this->basicOutcomeRequestSerializer = $basicOutcomeRequestSerializer ?? new BasicOutcomeRequestSerializer();
        $this->basicOutcomeResponseSerializer = $basicOutcomeResponseSerializer ?? new BasicOutcomeResponseSerializer();
        $this->logger = $logger ?? new NullLogger();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $validationResult = $this->validator->validate($request);

        if ($validationResult->hasError()) {
            $this->logger->error($validationResult->getError());

            return $this->httpResponseFactory->createResponse(401, null, [], $validationResult->getError());
        }

        try {
            $basicOutcomeResponse = $this->handler->handle(
                $this->basicOutcomeRequestSerializer->deserialize($request->getBody()->getContents())
            );

            return $this->httpResponseFactory->createResponse(
                200,
                null,
                [],
                $this->basicOutcomeResponseSerializer->serialize($basicOutcomeResponse)
            );
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return $this->httpResponseFactory->createResponse(500, null, [], 'Internal basic outcome service error');
        }
    }
}
