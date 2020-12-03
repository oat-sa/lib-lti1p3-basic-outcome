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
use OAT\Library\Lti1p3BasicOutcome\Factory\Response\BasicOutcomeResponseFactory;
use OAT\Library\Lti1p3BasicOutcome\Factory\Response\BasicOutcomeResponseFactoryInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\BasicOutcomeMessageInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\Request\BasicOutcomeRequestInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\Response\BasicOutcomeResponseInterface;
use OAT\Library\Lti1p3BasicOutcome\Service\Server\Processor\BasicOutcomeServiceServerProcessorInterface;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use Throwable;

class BasicOutcomeServiceServerHandler
{
    /** @var BasicOutcomeServiceServerProcessorInterface */
    private $processor;

    /** @var BasicOutcomeResponseFactoryInterface */
    private $factory;

    public function __construct(
        BasicOutcomeServiceServerProcessorInterface $processor,
        BasicOutcomeResponseFactoryInterface $factory = null
    ) {
        $this->processor = $processor;
        $this->factory = $factory ?? new BasicOutcomeResponseFactory();
    }

    /**
     * @throws LtiException
     */
    public function handle(BasicOutcomeRequestInterface $basicOutcomeRequest): BasicOutcomeResponseInterface
    {
        try {
            switch ($basicOutcomeRequest->getType()) {
                case BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT:
                    $result = $this->processor->processReplaceResult(
                        $basicOutcomeRequest->getSourcedId(),
                        $basicOutcomeRequest->getScore(),
                        $basicOutcomeRequest->getLanguage()
                    );
                    break;
                case BasicOutcomeMessageInterface::TYPE_READ_RESULT:
                    $result = $this->processor->processReadResult(
                        $basicOutcomeRequest->getSourcedId()
                    );
                    break;
                case BasicOutcomeMessageInterface::TYPE_DELETE_RESULT:
                    $result = $this->processor->processDeleteResult(
                        $basicOutcomeRequest->getSourcedId()
                    );
                    break;
                default:
                    throw new InvalidArgumentException(
                        sprintf('unsupported basic outcome type %s', $basicOutcomeRequest->getType())
                    );
            }

            return $this->factory->create(
                $basicOutcomeRequest->getType(),
                $result->isSuccess(),
                $basicOutcomeRequest->getIdentifier(),
                $basicOutcomeRequest->getType(),
                $result->getMessage(),
                $result->getScore(),
                $result->getLanguage()
            );

        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Error during basic outcome server handling: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }
}
