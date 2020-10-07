# Basic Outcome Tool - Basic Outcome Service Client

> How to use the [BasicOutcomeServiceClient](../src/Service/Client/BasicOutcomeServiceClient.php) to perform authenticated basic outcome service calls as a tool.

## Table of contents

- [Features](#features)
- [Usage](#usage)

## Features

This library provides a [ScoreServiceClient](../../src/Service/Score/Client/ScoreServiceClient.php) (based on the [core service client](https://github.com/oat-sa/lib-lti1p3-core/blob/master/doc/service/service-client.md)) that allow the following outcome operations:
- [read result](https://www.imsglobal.org/spec/lti-bo/v1p1#readresult)
- [replace result](https://www.imsglobal.org/spec/lti-bo/v1p1#replaceresult)
- [delete result](https://www.imsglobal.org/spec/lti-bo/v1p1#deleteresult)

You can use:
- `readResultFromPayload()` to [read a result](https://www.imsglobal.org/spec/lti-bo/v1p1#readresult) from a received LTI message payload (will use basic outcome claim)
- `readResult()` to [read a result](https://www.imsglobal.org/spec/lti-bo/v1p1#readresult) from a given basic outcome url and result sourced id
- `replaceResultFromPayload()` to [replace a result](https://www.imsglobal.org/spec/lti-bo/v1p1#replaceresult) from a received LTI message payload (will use basic outcome claim), with given score and language
- `replaceResult()` to [replace a result](https://www.imsglobal.org/spec/lti-bo/v1p1#replaceresult) for a given basic outcome url, result sourced id, score and language
- `deleteResultFromPayload()` to [delete a result](https://www.imsglobal.org/spec/lti-bo/v1p1#deleteresult) from a received LTI message payload (will use basic outcome claim)
- `deleteResult()` to [delete a result](https://www.imsglobal.org/spec/lti-bo/v1p1#deleteresult) for a given basic outcome url and result sourced id

## Usage

To read a result:

```php
<?php

use OAT\Library\Lti1p3BasicOutcome\Factory\BasicOutcomeResultCrawlerFactory;
use OAT\Library\Lti1p3BasicOutcome\Service\Client\BasicOutcomeServiceCLient;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;

// Related registration
/** @var RegistrationRepositoryInterface $registrationRepository */
$registration = $registrationRepository->find(...);

// Related LTI 1.3 message payload
/** @var LtiMessagePayloadInterface $payload */
$payload  = ...;

$client = new BasicOutcomeServiceCLient();

$result = $client->readResultFromPayload(
    $registration, // [required] as the tool, it will call the platform of this registration
    $payload       // [required] from the LTI message payload containing the basic outcome claim result sourced id (got at LTI launch)
);

// or you also can directly read a result from given URL and result sourced id (avoid claim construction)
$result = $client->readResult(
    $registration,                         // [required] as the tool, it will call the platform of this registration
    'https://example.com/basic-outcome',   // [required] to a given basic outcome service url
    'resultSourcedId'                      // [required] for a given result sourced id
);

if ($result->isSuccess()) {
    // you can work directly on the outcome response content
    $content = $result->getContent();
  
    // or use a prepared crawler to ease response processing
    $crawler = (new BasicOutcomeResultCrawlerFactory())->create($result);
    $crawler->filterXPath(...); // crawl outcome response
}
```

To replace a result:

```php
<?php

use OAT\Library\Lti1p3BasicOutcome\Factory\BasicOutcomeResultCrawlerFactory;
use OAT\Library\Lti1p3BasicOutcome\Service\Client\BasicOutcomeServiceCLient;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;

// Related registration
/** @var RegistrationRepositoryInterface $registrationRepository */
$registration = $registrationRepository->find(...);

// Related LTI 1.3 message payload
/** @var LtiMessagePayloadInterface $payload */
$payload  = ...;

$client = new BasicOutcomeServiceCLient();

$result = $client->replaceResultForPayload(
    $registration, // [required] as the tool, it will call the platform of this registration
    $payload,      // [required] for the LTI message payload containing the basic outcome claim result sourced id (got at LTI launch)
    0.5,           // [required] for a given score
    'en'           // [optional] for a given language
);

// or you also can directly replace a result on given URL, result sourced id, score and language (avoid claim construction)
$result = $client->replaceResult(
    $registration,                         // [required] as the tool, it will call the platform of this registration
    'https://example.com/basic-outcome',   // [required] to a given basic outcome service url
    'resultSourcedId',                     // [required] for a given result sourced id
    0.5,                                   // [required] for a given score
    'en'                                   // [optional] for a given language
);

if ($result->isSuccess()) {
    // you can work directly on the outcome response content
    $content = $result->getContent();
  
    // or use a prepared crawler to ease response processing
    $crawler = (new BasicOutcomeResultCrawlerFactory())->create($result);
    $crawler->filterXPath(...); // crawl outcome response
}
```

To delete a result:

```php
<?php

use OAT\Library\Lti1p3BasicOutcome\Factory\BasicOutcomeResultCrawlerFactory;
use OAT\Library\Lti1p3BasicOutcome\Service\Client\BasicOutcomeServiceCLient;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;

// Related registration
/** @var RegistrationRepositoryInterface $registrationRepository */
$registration = $registrationRepository->find(...);

// Related LTI 1.3 message payload
/** @var LtiMessagePayloadInterface $payload */
$payload  = ...;

$client = new BasicOutcomeServiceCLient();

$result = $client->deleteResultForPayload(
    $registration, // [required] as the tool, it will call the platform of this registration
    $payload       // [required] for the LTI message payload containing the basic outcome claim result sourced id (got at LTI launch)
);

// or you also can directly delete a result on given URL and result sourced id (avoid claim construction)
$result = $client->deleteResult(
    $registration,                         // [required] as the tool, it will call the platform of this registration
    'https://example.com/basic-outcome',   // [required] to a given basic outcome service url
    'resultSourcedId'                      // [required] for a given result sourced id
);

if ($result->isSuccess()) {
    // you can work directly on the outcome response content
    $content = $result->getContent();
  
    // or use a prepared crawler to ease response processing
    $crawler = (new BasicOutcomeResultCrawlerFactory())->create($result);
    $crawler->filterXPath(...); // crawl outcome response
}
```
