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

use OAT\Library\Lti1p3BasicOutcome\Result\BasicOutcomeResult;
use OAT\Library\Lti1p3BasicOutcome\Tests\Traits\TwigTestingTrait;
use PHPUnit\Framework\TestCase;

class BasicOutcomeResultTest extends TestCase
{
    use TwigTestingTrait;

    protected function setUp(): void
    {
        $this->setUpTwig();
    }

    public function testIsNotINSuccessWithInvalidResponse(): void
    {
        $subject = new BasicOutcomeResult('invalid');

        $this->assertFalse($subject->isSuccess());
    }

    public function testIsNotInSuccessWithFailureResponse(): void
    {
        $subject = new BasicOutcomeResult($this->twig->render('replace-result-response.xml.twig', [
            'status' => 'failure'
        ]));

        $this->assertFalse($subject->isSuccess());
    }

    public function testIsInSuccessWithSuccessResponse(): void
    {
        $subject = new BasicOutcomeResult($this->twig->render('replace-result-response.xml.twig'));

        $this->assertTrue($subject->isSuccess());
    }

    public function testResultCrawling(): void
    {
        $subject = new BasicOutcomeResult($this->twig->render('replace-result-response.xml.twig'));

        $path = '//imsx_POXEnvelopeResponse/imsx_POXHeader/imsx_POXResponseHeaderInfo/imsx_messageIdentifier';

        $this->assertEquals('789', $subject->getCrawler()->filterXPath($path)->text());
    }
}
