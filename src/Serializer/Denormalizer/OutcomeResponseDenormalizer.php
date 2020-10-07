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
use OAT\Library\Lti1p3BasicOutcome\Message\OutcomeResponse;
use OAT\Library\Lti1p3BasicOutcome\Message\Parts\Info;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Throwable;

class OutcomeResponseDenormalizer implements DenormalizerInterface
{
    /**
     * @throws LtiException
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): OutcomeResponse
    {
        try {
            $operation = $this->getOperation($data);

            return new OutcomeResponse(
                $data['imsx_POXHeader']['imsx_POXResponseHeaderInfo']['imsx_messageIdentifier'],
                $operation,
                $this->buildInfo($data),
                $this->buildBody($operation, $data)
            );
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot denormalize outcome response: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return is_a($type, OutcomeResponse::class, true);
    }

    private function getOperation(array $data): string
    {
        $body = $data['imsx_POXBody'] ?? [];

        if (array_key_exists('readResultResponse', $body)) {
            return MessageInterface::OPERATION_READ_RESULT;
        } elseif (array_key_exists('replaceResultResponse', $body)) {
            return MessageInterface::OPERATION_REPLACE_RESULT;
        } elseif (array_key_exists('deleteResultResponse', $body)) {
            return MessageInterface::OPERATION_DELETE_RESULT;
        } else {
            throw new InvalidArgumentException('Invalid outcome response operation');
        }
    }

    private function buildInfo(array $data): Info
    {
        $info = $data['imsx_POXHeader']['imsx_POXResponseHeaderInfo']['imsx_statusInfo'] ?? [];

        return new Info(
            $info['imsx_codeMajor'],
            $info['imsx_messageRefIdentifier'],
            $info['imsx_operationRefIdentifier'],
            $info['imsx_description'] ?? null
        );
    }

    private function buildBody(string $operation, array $data): ?Body
    {
        if ($operation !== MessageInterface::OPERATION_READ_RESULT) {
            return null;
        }

        $bodyPath = sprintf('%sResponse', $operation);

        return new Body(
            null,
            (float)$data['imsx_POXBody'][$bodyPath]['result']['resultScore']['textString'] ?? null,
            $data['imsx_POXBody'][$bodyPath]['result']['resultScore']['language'] ?? null
        );
    }
}

