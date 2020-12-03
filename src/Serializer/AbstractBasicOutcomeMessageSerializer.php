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

use Symfony\Component\DomCrawler\Crawler;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class AbstractBasicOutcomeMessageSerializer
{
    /** @var Environment */
    protected $twig;

    /** @var Crawler */
    protected $crawler;

    public function __construct(Environment $twig = null, Crawler $crawler = null)
    {
        $this->twig = $twig ?? new Environment(new FilesystemLoader(__DIR__ . '/../../templates'));
        $this->crawler = $crawler ?? new Crawler();
    }
}
