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

namespace OAT\Library\Lti1p3BasicOutcome\Tests\Unit\Factory\Response;

use OAT\Library\Lti1p3BasicOutcome\Factory\Response\BasicOutcomeResponseFactory;
use OAT\Library\Lti1p3BasicOutcome\Factory\Response\BasicOutcomeResponseFactoryInterface;
use OAT\Library\Lti1p3BasicOutcome\Generator\BasicOutcomeMessageIdentifierGeneratorInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\Response\BasicOutcomeResponseInterface;
use PHPUnit\Framework\TestCase;

class BasicOutcomeResponseFactoryTest extends TestCase
{
    public function testInstance(): void
    {
        $this->assertInstanceOf(BasicOutcomeResponseFactoryInterface::class, new BasicOutcomeResponseFactory());
    }

    public function testCreate(): void
    {
        $generatorMock = $this->createMock(BasicOutcomeMessageIdentifierGeneratorInterface::class);
        $generatorMock
            ->expects($this->once())
            ->method('generate')
            ->willReturn('generatedIdentifier');

        $subject = new BasicOutcomeResponseFactory($generatorMock);

        $result = $subject->create(
            'type',
            true,
            'refIdentifier',
            'refType',
            'description',
            0.42,
            'en'
        );

        $this->assertInstanceOf(BasicOutcomeResponseInterface::class, $result);

        $this->assertEquals('generatedIdentifier', $result->getIdentifier());
        $this->assertEquals('type', $result->getType());
        $this->assertTrue($result->isSuccess());
        $this->assertEquals('refIdentifier', $result->getReferenceRequestIdentifier());
        $this->assertEquals('refType', $result->getReferenceRequestType());
        $this->assertEquals('description', $result->getDescription());
        $this->assertEquals(0.42, $result->getScore());
        $this->assertEquals('en', $result->getLanguage());
    }

    public function testCreateWithGivenIdentifier(): void
    {
        $subject = new BasicOutcomeResponseFactory();

        $result = $subject->create(
            'type',
            true,
            'refIdentifier',
            'refType',
            'description',
            0.42,
            'en',
            'identifier'
        );

        $this->assertEquals('identifier', $result->getIdentifier());
    }
}
