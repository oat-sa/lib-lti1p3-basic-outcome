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

namespace OAT\Library\Lti1p3BasicOutcome\Serializer\Denormalizer;

use InvalidArgumentException;
use OAT\Library\Lti1p3BasicOutcome\Message\MessageInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\Parts\Body;
use OAT\Library\Lti1p3BasicOutcome\Message\OutcomeRequest;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Throwable;

class OutcomeRequestDenormalizer implements DenormalizerInterface
{
    /**
     * @throws LtiException
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): OutcomeRequest
    {
        try {
            $operation = $this->getOperation($data);

            return new OutcomeRequest(
                $data['imsx_POXHeader']['imsx_POXRequestHeaderInfo']['imsx_messageIdentifier'],
                $operation,
                $this->buildBody($operation, $data)
            );
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot denormalize request: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return is_a($type, OutcomeRequest::class, true);
    }

    private function getOperation(array $data): string
    {
        $body = $data['imsx_POXBody'] ?? [];

        if (array_key_exists('readResultRequest', $body)) {
            return MessageInterface::OPERATION_READ_RESULT;
        } elseif (array_key_exists('replaceResultRequest', $body)) {
            return MessageInterface::OPERATION_REPLACE_RESULT;
        } elseif (array_key_exists('deleteResultRequest', $body)) {
            return MessageInterface::OPERATION_DELETE_RESULT;
        } else {
            throw new InvalidArgumentException('Invalid outcome request operation');
        }
    }

    private function buildBody(string $operation, array $data): ?Body
    {
        $bodyPath = sprintf('%sRequest', $operation);

        $sourcedId = $data['imsx_POXBody'][$bodyPath]['resultRecord']['sourcedGUID']['sourcedId'] ?? null;

        if ($operation === MessageInterface::OPERATION_REPLACE_RESULT) {
            return new Body(
                $sourcedId,
                (float)$data['imsx_POXBody'][$bodyPath]['resultRecord']['result']['resultScore']['textString'] ?? null,
                $data['imsx_POXBody'][$bodyPath]['resultRecord']['result']['resultScore']['language'] ?? null
            );
        }

        return new Body($sourcedId);
    }
}

