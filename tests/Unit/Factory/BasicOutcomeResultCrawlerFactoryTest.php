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

namespace OAT\Library\Lti1p3BasicOutcome\Tests\Unit\Factory;

use OAT\Library\Lti1p3BasicOutcome\Factory\BasicOutcomeResultCrawlerFactory;
use OAT\Library\Lti1p3BasicOutcome\Result\BasicOutcomeResultFactory;
use OAT\Library\Lti1p3BasicOutcome\Result\BasicOutcomeResultFactoryInterface;
use OAT\Library\Lti1p3BasicOutcome\Tests\Traits\TwigTestingTrait;
use PHPUnit\Framework\TestCase;

class BasicOutcomeResultCrawlerFactoryTest extends TestCase
{
    use TwigTestingTrait;

    /** @var BasicOutcomeResultFactoryInterface */
    private $factory;

    protected function setUp(): void
    {
        $this->setUpTwig();

        $this->factory = new BasicOutcomeResultFactory();
    }
    public function testCrawling(): void
    {
        $subject = new BasicOutcomeResultCrawlerFactory();

        $crawler = $subject->create(
            $this->factory->create($this->twig->render('replace-result-response.xml.twig'))
        );

        $this->assertEquals(
            '789',
            $crawler->filterXPath('//imsx_POXEnvelopeResponse/imsx_POXHeader/imsx_POXResponseHeaderInfo/imsx_messageIdentifier')->text()
        );
    }
}
