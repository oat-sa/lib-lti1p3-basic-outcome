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

namespace OAT\Library\Lti1p3BasicOutcome\Serializer;

use OAT\Library\Lti1p3BasicOutcome\Message\OutcomeRequest;
use OAT\Library\Lti1p3BasicOutcome\Message\OutcomeResponse;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Denormalizer\OutcomeRequestDenormalizer;
use OAT\Library\Lti1p3BasicOutcome\Serializer\Denormalizer\OutcomeResponseDenormalizer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class OutcomeMessageSerializer
{
    /** @var Serializer */
    protected $serializer;

    /** @var Environment */
    protected $twig;

    public function __construct(Serializer $serializer = null, Environment $twig = null)
    {
        $this->serializer = $serializer ?? $this->buildSerializer();
        $this->twig = $twig ?? $this->buildTwig();
    }
    public function serializeRequest(OutcomeRequest $outcomeRequest): string
    {
        return $this->twig->render('outcome-request.xml.twig', ['message' => $outcomeRequest]);
    }

    public function deserializeRequest(string $data): OutcomeRequest
    {
        return $this->serializer->deserialize($data, OutcomeRequest::class, 'xml');
    }

    public function serializeResponse(OutcomeResponse $outcomeResponse): string
    {
        return $this->twig->render('outcome-response.xml.twig', ['message' => $outcomeResponse]);
    }

    public function deserializeResponse(string $data): OutcomeResponse
    {
        return $this->serializer->deserialize($data, OutcomeResponse::class, 'xml');
    }

    private function buildSerializer(): Serializer
    {
        return new Serializer(
            [
                new OutcomeRequestDenormalizer(),
                new OutcomeResponseDenormalizer(),
            ],
            [
                new XmlEncoder()
            ]
        );
    }

    private function buildTwig(): Environment
    {
        return new Environment(new FilesystemLoader(__DIR__ . '/../../templates'));
    }
}
