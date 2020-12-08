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

namespace OAT\Library\Lti1p3BasicOutcome\Serializer\Request;

use OAT\Library\Lti1p3BasicOutcome\Factory\Request\BasicOutcomeRequestFactory;
use OAT\Library\Lti1p3BasicOutcome\Factory\Request\BasicOutcomeRequestFactoryInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\BasicOutcomeMessageInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\Request\BasicOutcomeRequestInterface;
use OAT\Library\Lti1p3BasicOutcome\Serializer\AbstractBasicOutcomeMessageSerializer;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;
use Twig\Environment;

class BasicOutcomeRequestSerializer extends AbstractBasicOutcomeMessageSerializer implements BasicOutcomeRequestSerializerInterface
{
    /** @var BasicOutcomeRequestFactoryInterface */
    private $factory;

    public function __construct(
        BasicOutcomeRequestFactoryInterface $factory = null,
        Environment $twig = null,
        Crawler $crawler = null
    ) {
        parent::__construct($twig, $crawler);

        $this->factory = $factory ?? new BasicOutcomeRequestFactory();
    }

    /**
     * @throws LtiException
     */
    public function serialize(BasicOutcomeRequestInterface $basicOutcomeRequest): string
    {
        try {
            return $this->twig->render(
                sprintf('request/%sRequest.xml.twig', $basicOutcomeRequest->getType()),
                [
                    'request' => $basicOutcomeRequest
                ]
            );
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot serialize basic outcome request: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiException
     */
    public function deserialize(string $xml): BasicOutcomeRequestInterface
    {
        try {
            $this->crawler->clear();
            $this->crawler->add($xml);

            $identifier = $this->crawler
                ->filterXPath('//imsx_POXEnvelopeRequest/imsx_POXHeader/imsx_POXRequestHeaderInfo/imsx_messageIdentifier')
                ->text();

            $type = substr(
                $this->crawler->filterXPath('//imsx_POXEnvelopeRequest/imsx_POXBody/*')->nodeName(),
                0,
                -7
            );

            $sourcedId = $this->crawler
                ->filterXPath('//imsx_POXEnvelopeRequest/imsx_POXBody/*/resultRecord/sourcedGUID/sourcedId')
                ->text();

            $score = $language = null;

            if ($type === BasicOutcomeMessageInterface::TYPE_REPLACE_RESULT) {
                $scoreData = $this->crawler
                    ->filterXPath('//imsx_POXEnvelopeRequest/imsx_POXBody/*/resultRecord/result/resultScore/textString')
                    ->text();

                $score = empty($scoreData) ? null : floatval($scoreData);

                $languageData = $this->crawler
                    ->filterXPath('//imsx_POXEnvelopeRequest/imsx_POXBody/*/resultRecord/result/resultScore/language')
                    ->text();

                $language = empty($languageData) ? null : $languageData;
            }

            return $this->factory->create(
                $type,
                $sourcedId,
                $score,
                $language,
                $identifier
            );

        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot deserialize basic outcome request: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }
}
