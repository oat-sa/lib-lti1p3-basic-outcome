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

namespace OAT\Library\Lti1p3BasicOutcome\Tests\Unit\Service\Server\Processor\Result;

use OAT\Library\Lti1p3BasicOutcome\Service\Server\Processor\Result\BasicOutcomeServiceServerProcessorResult;
use PHPUnit\Framework\TestCase;

class BasicOutcomeServiceServerProcessorResultTest extends TestCase
{
    /** @var BasicOutcomeServiceServerProcessorResult */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new BasicOutcomeServiceServerProcessorResult(
            true,
            'message',
            0.42,
            'en'
        );
    }

    public function testGetters(): void
    {
        $this->assertTrue($this->subject->isSuccess());
        $this->assertEquals('message', $this->subject->getMessage());
        $this->assertEquals(0.42, $this->subject->getScore());
        $this->assertEquals('en', $this->subject->getLanguage());
    }
}
