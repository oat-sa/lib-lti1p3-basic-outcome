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

namespace OAT\Library\Lti1p3BasicOutcome\Service\Server\Processor\Result;

class BasicOutcomeServiceServerProcessorResult
{
    /** @var bool */
    private $success;

    /** @var string|null */
    private $message;

    /** @var float|null */
    private $score;

    /** @var string|null */
    private $language;

    public function __construct(bool $success, string $message = null, float $score = null, string $language = null)
    {
        $this->success = $success;
        $this->message = $message;
        $this->score = $score;
        $this->language = $language;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }
}
