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

namespace OAT\Library\Lti1p3BasicOutcome\Factory;

use OAT\Library\Lti1p3BasicOutcome\Generator\MessageIdentifierGenerator;
use OAT\Library\Lti1p3BasicOutcome\Generator\MessageIdentifierGeneratorInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\MessageInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\OutcomeRequest;
use OAT\Library\Lti1p3BasicOutcome\Message\Parts\Body;

class OutcomeMessageFactory
{
    /** @var MessageIdentifierGeneratorInterface */
    private $generator;

    public function __construct(MessageIdentifierGeneratorInterface $generator = null)
    {
        $this->generator = $generator ?? new MessageIdentifierGenerator();
    }

    public function createReadResultRequest(string $sourcedId): OutcomeRequest
    {
        return new OutcomeRequest(
            $this->generator->generate(),
            MessageInterface::OPERATION_READ_RESULT,
            new Body($sourcedId)
        );
    }

    public function createReplaceResultRequest(string $sourcedId, float $score, string $language = 'en'): OutcomeRequest
    {
        return new OutcomeRequest(
            $this->generator->generate(),
            MessageInterface::OPERATION_REPLACE_RESULT,
            new Body($sourcedId, $score, $language)
        );
    }

    public function createDeleteResultRequest(string $sourcedId): OutcomeRequest
    {
        return new OutcomeRequest(
            $this->generator->generate(),
            MessageInterface::OPERATION_DELETE_RESULT,
            new Body($sourcedId)
        );
    }
}

