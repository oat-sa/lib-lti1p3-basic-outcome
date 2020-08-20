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

namespace OAT\Library\Lti1p3BasicOutcome\Tests\Unit\Result;

use OAT\Library\Lti1p3BasicOutcome\Result\BasicOutcomeResultFactory;
use OAT\Library\Lti1p3BasicOutcome\Result\BasicOutcomeResultInterface;
use OAT\Library\Lti1p3BasicOutcome\Tests\Traits\TwigTestingTrait;
use PHPUnit\Framework\TestCase;

class BasicOutcomeResultFactoryTest extends TestCase
{
    use TwigTestingTrait;

    protected function setUp(): void
    {
        $this->setUpTwig();
    }

    public function testCreate(): void
    {
        $subject = new BasicOutcomeResultFactory();

        $result = $subject->create($this->twig->render('replace-result-response.xml.twig'));
        $this->assertInstanceOf(BasicOutcomeResultInterface::class, $result);
        $this->assertTrue($result->isSuccess());

        $result = $subject->create($this->twig->render('replace-result-response.xml.twig', [
            'status' => 'failure'
        ]));
        $this->assertInstanceOf(BasicOutcomeResultInterface::class, $result);
        $this->assertFalse($result->isSuccess());
    }
}
