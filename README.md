# LTI 1.3 Basic Outcome Library

> PHP library for [LTI 1.3 Basic Outcome](https://www.imsglobal.org/spec/lti-bo/v1p1) implementations as tool, based on [lti1p3-core library](https://github.com/oat-sa/lib-lti1p3-core).

# Table of contents

- [Specifications](#specifications)
- [Installation](#installation)
- [Usage](#usage)
- [Tests](#tests)

## Specifications

- [LTI 1.3 Basic Outcome Service Integration](https://www.imsglobal.org/spec/lti-bo/v1p1#integration-with-lti-1-3)
- [IMS LTI 1.3 Core](http://www.imsglobal.org/spec/lti/v1p3)
- [IMS Security](https://www.imsglobal.org/spec/security/v1p0)

## Installation

```console
$ composer require oat-sa/lib-lti1p3-basic-outcome
```

## Usage

This library offers a [BasicOutcomeServiceClient](src/Service/Client/BasicOutcomeServiceClient.php), ready to be used to send basic outcomes to a registered platform, following [LTI 1.3 services security specifications](https://www.imsglobal.org/spec/security/v1p0/#securing_web_services).

Supported outcomes operations:
- [read result](https://www.imsglobal.org/spec/lti-bo/v1p1#readresult)
- [replace result](https://www.imsglobal.org/spec/lti-bo/v1p1#replaceresult)
- [delete result](https://www.imsglobal.org/spec/lti-bo/v1p1#deleteresult)

Usage example with the `replaceResult()` operation:
```php
<?php

use OAT\Library\Lti1p3BasicOutcome\Service\Client\BasicOutcomeServiceCLient;
use OAT\Library\Lti1p3Core\Message\Claim\BasicOutcomeClaim;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;

// Build basic outcome claim (or get it from the previous LTI launch)
/** @var BasicOutcomeClaim $claim */
$claim = new BasicOutcomeClaim(...);

// Get related registration of the outcome
/** @var RegistrationRepositoryInterface $registrationRepository */
$registration = $registrationRepository->find(...);

$client = new BasicOutcomeServiceCLient();

$result = $client->replaceResult(
    $registration,  // will use the access token endpoint of this registration's platform
    $claim,         // will send the outcome to the claim outcome service url
    0.7,            // score to replace (float between 0 and 1)
    'fr'            // optional language (default='en')
);

if ($result->isSuccess()) {
    $result->getCrawler()->filterXPath(...); // crawl outcome response
}
```

**Note**: the shortcut methods (`readResult`, `replaceResult` and `deleteResult`) use [twig templates](templates) to generate the outcome request body.
You can provide your own request body by using the `sendBasicOutcome()` method directly.

## Tests

To run tests:

```console
$ vendor/bin/phpunit
```
**Note**: see [phpunit.xml.dist](phpunit.xml.dist) for available test suites.