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

namespace OAT\Library\Lti1p3BasicOutcome\Message\Parts;

class Info
{
    /** @var string */
    private $status;

    /** @var string */
    private $messageRefIdentifier;

    /** @var string */
    private $operationRefIdentifier;

    /** @var string|null */
    private $description;

    public function __construct(
        string $status,
        string $messageRefIdentifier,
        string $operationRefIdentifier,
        string $description = null
    ) {
        $this->status = $status;
        $this->messageRefIdentifier = $messageRefIdentifier;
        $this->operationRefIdentifier = $operationRefIdentifier;
        $this->description = $description;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getMessageRefIdentifier(): ?string
    {
        return $this->messageRefIdentifier;
    }

    public function getOperationRefIdentifier(): ?string
    {
        return $this->operationRefIdentifier;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}

