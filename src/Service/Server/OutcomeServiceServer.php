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

use OAT\Library\Lti1p3BasicOutcome\Message\MessageInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\OutcomeResponse;
use OAT\Library\Lti1p3BasicOutcome\Message\Parts\Info;
use OAT\Library\Lti1p3BasicOutcome\Serializer\OutcomeMessageSerializer;
use OAT\Library\Lti1p3BasicOutcome\Service\Server\Handler\OutcomeServiceServerHandlerInterface;
use OAT\Library\Lti1p3BasicOutcome\Service\Server\Handler\OutcomeServiceServerHandlerResult;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @see https://www.imsglobal.org/spec/lti-bo/v1p1#integration-with-lti-1-3
 */
class OutcomeServiceServer
{
    /** @var OutcomeServiceServerHandlerInterface */
    private $handler;

    /** @var OutcomeMessageSerializer */
    private $messageSerializer;

    public function __construct(
        OutcomeServiceServerHandlerInterface $handler,
        OutcomeMessageSerializer $messageSerializer = null
    ) {
        $this->$handler = $handler;
        $this->messageSerializer = $messageSerializer ?? new OutcomeMessageSerializer();
    }

    public function handle(ServerRequestInterface $request): OutcomeResponse
    {
        $outcomeRequest = $this->messageSerializer->deserializeRequest($request->getBody()->__toString());

        switch ($outcomeRequest->getOperation()) {
            case MessageInterface::OPERATION_READ_RESULT:
                $result = $this->handler->handleReadResult(
                    $outcomeRequest->getBody()->getSourcedId()
                );
                break;
            case MessageInterface::OPERATION_REPLACE_RESULT:
                $result = $this->handler->handleReplaceResult(
                    $outcomeRequest->getBody()->getSourcedId(),
                    $outcomeRequest->getBody()->getScore(),
                    $outcomeRequest->getBody()->getLanguage()
                );
                break;
            case MessageInterface::OPERATION_DELETE_RESULT:
                $result = $this->handler->handleDeleteResult(
                    $outcomeRequest->getBody()->getSourcedId()
                );
                break;
            default:
                $result = new OutcomeServiceServerHandlerResult(false, 'invalid operation');
        }

        return new OutcomeResponse(
            'some id',
            $outcomeRequest->getOperation(),
            new Info(
                $result->isSuccess() ? 'success' : 'failure',
                $outcomeRequest->getIdentifier(),
                $outcomeRequest->getOperation(),
                'description'
            )
        );
    }
}
