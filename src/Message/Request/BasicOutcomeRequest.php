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

namespace OAT\Library\Lti1p3BasicOutcome\Message\Request;

use OAT\Library\Lti1p3BasicOutcome\Message\AbstractBasicOutcomeMessage;

class BasicOutcomeRequest extends AbstractBasicOutcomeMessage implements BasicOutcomeRequestInterface
{
    /** @var string */
    private $sourcedId;

    /** @var float|null */
    private $score;

    /** @var string|null */
    private $language;

    public function __construct(
        string $identifier,
        string $type,
        string $sourcedId,
        ?float $score = null,
        ?string $language = null
    ) {
        parent::__construct($identifier, $type);

        $this->sourcedId = $sourcedId;
        $this->score = $score;
        $this->language = $language;
    }

    public function getSourcedId(): string
    {
        return $this->sourcedId;
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
