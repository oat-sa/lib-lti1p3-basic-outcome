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

namespace OAT\Library\Lti1p3BasicOutcome\Tests\Unit\Serializer\Request;

use OAT\Library\Lti1p3BasicOutcome\Message\BasicOutcomeMessageInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\Request\BasicOutcomeRequest;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Request\BasicOutcomeRequestSerializer;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Request\BasicOutcomeRequestSerializerInterface;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class BasicOutcomeRequestSerializerTest extends TestCase
{
    /** @var Environment */
    private $twig;

    /** @var BasicOutcomeRequestSerializerInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->twig = new Environment(new FilesystemLoader(__DIR__ . '/../../../../templates'));

        $this->subject = new BasicOutcomeRequestSerializer(null, $this->twig);
    }

    public function testInstance(): void
    {
        $this->assertInstanceOf(BasicOutcomeRequestSerializerInterface::class, new BasicOutcomeRequestSerializer());
    }

    public function testSerializationSuccess(): void
    {
        $request = new BasicOutcomeRequest(
            'identifier',
            BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT,
            'sourcedId',
            0.42,
            'en'
        );

        $this->assertEquals(
            $this->twig->render('request/replaceResultRequest.xml.twig', ['request' => $request]),
            $this->subject->serialize($request)
        );
    }

    public function testSerializationFailure(): void
    {
        $this->expectException(LtiException::class);
        $this->expectExceptionMessage('Cannot serialize basic outcome request: Unable to find template "request/invalidRequest.xml.twig');

        $request = new BasicOutcomeRequest(
            'identifier',
            'invalid',
            'sourcedId',
            0.42,
            'en'
        );

        $this->subject->serialize($request);
    }

    public function testDeserializationSuccess(): void
    {
        $request = new BasicOutcomeRequest(
            'identifier',
            BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT,
            'sourcedId',
            0.42,
            'en'
        );

        $result = $this->subject->deserialize(
            $this->twig->render('request/replaceResultRequest.xml.twig', ['request' => $request])
        );

        $this->assertEquals($request, $result);
    }

    public function testDeserializationFailure(): void
    {
        $this->expectException(LtiException::class);
        $this->expectExceptionMessage('Cannot deserialize basic outcome request: The current node list is empty.');

        $this->subject->deserialize('invalid');
    }
}
