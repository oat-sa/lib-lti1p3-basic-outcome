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

namespace OAT\Library\Lti1p3BasicOutcome\Message;

use OAT\Library\Lti1p3BasicOutcome\Message\Parts\Body;

abstract class AbstractMessage implements MessageInterface
{
    /** @var string */
    protected $identifier;

    /** @var string */
    protected $operation;

    /** @var Body|null */
    protected $body;

    public function __construct(string $identifier, string $operation, Body $body = null)
    {
        $this->identifier = $identifier;
        $this->operation = $operation;
        $this->body = $body;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getBody(): ?Body
    {
        return $this->body;
    }
}
