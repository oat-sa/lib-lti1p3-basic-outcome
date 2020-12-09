# Basic Outcome Platform - Basic Outcome Service Server

> How to use the [BasicOutcomeServiceServer](../src/Service/Server/BasicOutcomeServiceServer.php) to serve authenticated Basic Outcome service endpoints as a platform.

## Table of contents

- [Features](#features)
- [Usage](#usage)

## Features

This library provides a [BasicOutcomeServiceServer](../src/Service/Server/BasicOutcomeServiceServer.php) ready to handle basic outcome operations.

- it accepts a [PSR7 ServerRequestInterface](https://www.php-fig.org/psr/psr-7/#321-psrhttpmessageserverrequestinterface) containing the basic outcome request,
- leverages the [required IMS LTI 1.3 service authentication](https://www.imsglobal.org/spec/security/v1p0/#securing_web_services),
- and returns a [PSR7 ResponseInterface](https://www.php-fig.org/psr/psr-7/#33-psrhttpmessageresponseinterface) containing the basic outcome response

## Usage

First, you need to provide a [BasicOutcomeServiceServerProcessorInterface](../src/Service/Server/Processor/BasicOutcomeServiceServerProcessorInterface.php) implementation, in charge to process the basic outcome operations.

```php
<?php

use OAT\Library\Lti1p3BasicOutcome\Service\Server\Processor\BasicOutcomeServiceServerProcessorInterface;
use OAT\Library\Lti1p3BasicOutcome\Service\Server\Processor\BasicOutcomeServiceServerProcessorResult;

/** @var BasicOutcomeServiceServerProcessorInterface $processor */
$processor = new class() implements BasicOutcomeServiceServerProcessorInterface 
{
    public function processReadResult(string $sourcedId) : BasicOutcomeServiceServerProcessorResult
    {
        // Logic for readResult basic outcome operations
    }

    public function processReplaceResult(string $sourcedId,float $score,string $language = 'en') : BasicOutcomeServiceServerProcessorResult
    {
        // Logic for replaceResult basic outcome operations
    }

    public function processDeleteResult(string $sourcedId) : BasicOutcomeServiceServerProcessorResult
    {
        // Logic for deleteResult basic outcome operations
    }
};
```

You can then construct the [BasicOutcomeServiceServer](../src/Service/Server/BasicOutcomeServiceServer.php) with:
- the [AccessTokenRequestValidator](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Service/Server/Validator/AccessTokenRequestValidator.php) (from lti1p3-core)
- the [BasicOutcomeServiceServerHandler](../src/Service/Server/Handler/BasicOutcomeServiceServerHandler.php) that will use your `BasicOutcomeServiceServerProcessorInterface` implementation

To finally expose it to requests:
```php
<?php

use OAT\Library\Lti1p3BasicOutcome\Service\Server\BasicOutcomeServiceServer;
use OAT\Library\Lti1p3BasicOutcome\Service\Server\Handler\BasicOutcomeServiceServerHandler;
use OAT\Library\Lti1p3BasicOutcome\Service\Server\Processor\BasicOutcomeServiceServerProcessorInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use Psr\Http\Message\ServerRequestInterface;

/** @var RegistrationRepositoryInterface $repository */
$repository = ...

/** @var BasicOutcomeServiceServerProcessorInterface $processor */
$processor = ...

$validator = new AccessTokenRequestValidator($repository);

$handler = new BasicOutcomeServiceServerHandler($processor);

$basicOutcomeServiceServer = new BasicOutcomeServiceServer($validator, $handler);

/** @var ServerRequestInterface $request */
$request = ...

// Generates a response containing the basic outcome operation result
$response = $basicOutcomeServiceServer->handle($request);
```
