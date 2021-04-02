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

namespace OAT\Library\Lti1p3BasicOutcome\Serializer\Response;

use OAT\Library\Lti1p3BasicOutcome\Factory\Response\BasicOutcomeResponseFactory;
use OAT\Library\Lti1p3BasicOutcome\Factory\Response\BasicOutcomeResponseFactoryInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\BasicOutcomeMessageInterface;
use OAT\Library\Lti1p3BasicOutcome\Message\Response\BasicOutcomeResponseInterface;
use OAT\Library\Lti1p3BasicOutcome\Serializer\AbstractBasicOutcomeMessageSerializer;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;
use Twig\Environment;

class BasicOutcomeResponseSerializer extends AbstractBasicOutcomeMessageSerializer implements BasicOutcomeResponseSerializerInterface
{
    /** @var BasicOutcomeResponseFactoryInterface */
    private $factory;

    public function __construct(
        ?BasicOutcomeResponseFactoryInterface $factory = null,
        ?Environment $twig = null,
        ?Crawler $crawler = null
    ) {
        parent::__construct($twig, $crawler);

        $this->factory = $factory ?? new BasicOutcomeResponseFactory();
    }

    /**
     * @throws LtiException
     */
    public function serialize(BasicOutcomeResponseInterface $basicOutcomeResponse): string
    {
        try {
            return $this->twig->render(
                sprintf('response/%sResponse.xml.twig', $basicOutcomeResponse->getType()),
                [
                    'response' => $basicOutcomeResponse
                ]
            );
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot serialize basic outcome response: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiException
     */
    public function deserialize(string $xml): BasicOutcomeResponseInterface
    {
        try {
            $this->crawler->clear();
            $this->crawler->add($xml);

            $identifier = $this->crawler
                ->filterXPath('//imsx_POXEnvelopeResponse/imsx_POXHeader/imsx_POXResponseHeaderInfo/imsx_messageIdentifier')
                ->text();

            $type = substr(
                $this->crawler->filterXPath('//imsx_POXEnvelopeResponse/imsx_POXBody/*')->nodeName(),
                0,
                -8
            );

            $isSuccess = $this->crawler
                ->filterXPath('//imsx_POXEnvelopeResponse/imsx_POXHeader/imsx_POXResponseHeaderInfo/imsx_statusInfo/imsx_codeMajor')
                ->text() == 'success';

            $referenceRequestIdentifier = $this->crawler
                ->filterXPath('//imsx_POXEnvelopeResponse/imsx_POXHeader/imsx_POXResponseHeaderInfo/imsx_statusInfo/imsx_messageRefIdentifier')
                ->text();

            $referenceRequestType = $this->crawler
                ->filterXPath('//imsx_POXEnvelopeResponse/imsx_POXHeader/imsx_POXResponseHeaderInfo/imsx_statusInfo/imsx_operationRefIdentifier')
                ->text();

            $description = $this->crawler
                ->filterXPath('//imsx_POXEnvelopeResponse/imsx_POXHeader/imsx_POXResponseHeaderInfo/imsx_statusInfo/imsx_description')
                ->text();

            $score = $language = null;

            if ($type === BasicOutcomeMessageInterface::TYPE_READ_RESULT) {
                $scoreData = $this->crawler
                    ->filterXPath('//imsx_POXEnvelopeResponse/imsx_POXBody/readResultResponse/result/resultScore/textString')
                    ->text();

                $score = empty($scoreData) ? null : floatval($scoreData);

                $languageData = $this->crawler
                    ->filterXPath('//imsx_POXEnvelopeResponse/imsx_POXBody/readResultResponse/result/resultScore/language')
                    ->text();

                $language = empty($languageData) ? null : $languageData;
            }

            return $this->factory->create(
                $type,
                $isSuccess,
                $referenceRequestIdentifier,
                $referenceRequestType,
                $description,
                $score,
                $language,
                $identifier
            );

        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot deserialize basic outcome response: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }
}
