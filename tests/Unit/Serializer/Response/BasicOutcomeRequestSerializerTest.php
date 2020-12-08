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

namespace OAT\Library\Lti1p3BasicOutcome\Tests\Unit\Serializer\Response;

use OAT\Library\Lti1p3BasicOutcome\Message\BasicOutcomeMessageInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\Response\BasicOutcomeResponse;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Response\BasicOutcomeResponseSerializer;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Response\BasicOutcomeResponseSerializerInterface;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class BasicOutcomeResponseSerializerTest extends TestCase
{
    /** @var Environment */
    private $twig;

    /** @var BasicOutcomeResponseSerializerInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->twig = new Environment(new FilesystemLoader(__DIR__ . '/../../../../templates'));

        $this->subject = new BasicOutcomeResponseSerializer(null, $this->twig);
    }

    public function testInstance(): void
    {
        $this->assertInstanceOf(BasicOutcomeResponseSerializerInterface::class, new BasicOutcomeResponseSerializer());
    }

    public function testSerializationSuccess(): void
    {
        $response = new BasicOutcomeResponse(
            'identifier',
            BasicOutcomeMessageInterface::TYPE_READ_RESULT,
            true,
            'refIdentifier',
            BasicOutcomeMessageInterface::TYPE_READ_RESULT,
            'description',
            0.42,
            'en'
        );

        $this->assertEquals(
            $this->twig->render('response/readResultResponse.xml.twig', ['response' => $response]),
            $this->subject->serialize($response)
        );
    }

    public function testSerializationFailure(): void
    {
        $this->expectException(LtiException::class);
        $this->expectExceptionMessage('Cannot serialize basic outcome response: Unable to find template "response/invalidResponse.xml.twig');

        $response = new BasicOutcomeResponse(
            'identifier',
            'invalid',
            true,
            'refIdentifier',
            'invalid',
            'description',
            0.42,
            'en'
        );

        $this->subject->serialize($response);
    }

    public function testDeserializationSuccess(): void
    {
        $response = new BasicOutcomeResponse(
            'identifier',
            BasicOutcomeMessageInterface::TYPE_READ_RESULT,
            true,
            'refIdentifier',
            BasicOutcomeMessageInterface::TYPE_READ_RESULT,
            'description',
            0.42,
            'en'
        );

        $result = $this->subject->deserialize(
            $this->twig->render('response/readResultResponse.xml.twig', ['response' => $response])
        );

        $this->assertEquals($response, $result);
    }

    public function testDeserializationFailure(): void
    {
        $this->expectException(LtiException::class);
        $this->expectExceptionMessage('Cannot deserialize basic outcome response: The current node list is empty.');

        $this->subject->deserialize('invalid');
    }
}
