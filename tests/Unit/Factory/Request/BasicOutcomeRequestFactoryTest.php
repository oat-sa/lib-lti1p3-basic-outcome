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

namespace OAT\Library\Lti1p3BasicOutcome\Tests\Unit\Factory\Request;

use OAT\Library\Lti1p3BasicOutcome\Factory\Request\BasicOutcomeRequestFactory;
use OAT\Library\Lti1p3BasicOutcome\Factory\Request\BasicOutcomeRequestFactoryInterface;
use OAT\Library\Lti1p3BasicOutcome\Generator\BasicOutcomeMessageIdentifierGeneratorInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\Request\BasicOutcomeRequestInterface;
use PHPUnit\Framework\TestCase;

class BasicOutcomeRequestFactoryTest extends TestCase
{
    public function testInstance(): void
    {
        $this->assertInstanceOf(BasicOutcomeRequestFactoryInterface::class, new BasicOutcomeRequestFactory());
    }

    public function testCreate(): void
    {
        $generatorMock = $this->createMock(BasicOutcomeMessageIdentifierGeneratorInterface::class);
        $generatorMock
            ->expects($this->once())
            ->method('generate')
            ->willReturn('generatedIdentifier');

        $subject = new BasicOutcomeRequestFactory($generatorMock);

        $result = $subject->create(
            'type',
            'sourcedId',
            0.42,
            'en'
        );

        $this->assertInstanceOf(BasicOutcomeRequestInterface::class, $result);

        $this->assertEquals('generatedIdentifier', $result->getIdentifier());
        $this->assertEquals('type', $result->getType());
        $this->assertEquals('sourcedId', $result->getSourcedId());
        $this->assertEquals(0.42, $result->getScore());
        $this->assertEquals('en', $result->getLanguage());
    }

    public function testCreateWithGivenIdentifier(): void
    {
        $subject = new BasicOutcomeRequestFactory();

        $result = $subject->create(
            'type',
            'sourcedId',
            0.42,
            'en',
            'identifier'
        );

        $this->assertEquals('identifier', $result->getIdentifier());
    }
}
