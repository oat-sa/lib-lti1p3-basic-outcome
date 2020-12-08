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

namespace OAT\Library\Lti1p3BasicOutcome\Tests\Unit\Message\Response;

use OAT\Library\Lti1p3BasicOutcome\Message\Response\BasicOutcomeResponse;
use OAT\Library\Lti1p3BasicOutcome\Message\Response\BasicOutcomeResponseInterface;
use PHPUnit\Framework\TestCase;

class BasicOutcomeResponseTest extends TestCase
{
    /** @var BasicOutcomeResponseInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new BasicOutcomeResponse(
            'identifier',
            'type',
            true,
            'refIdentifier',
            'refType',
            'description',
            0.42,
            'en'
        );
    }

    public function testInstance(): void
    {
        $this->assertInstanceOf(BasicOutcomeResponseInterface::class, $this->subject);
    }

    public function testGetters(): void
    {
        $this->assertEquals('identifier', $this->subject->getIdentifier());
        $this->assertEquals('type', $this->subject->getType());
        $this->assertTrue($this->subject->isSuccess());
        $this->assertEquals('refIdentifier', $this->subject->getReferenceRequestIdentifier());
        $this->assertEquals('refType', $this->subject->getReferenceRequestType());
        $this->assertEquals('description', $this->subject->getDescription());
        $this->assertEquals(0.42, $this->subject->getScore());
        $this->assertEquals('en', $this->subject->getLanguage());
    }
}
