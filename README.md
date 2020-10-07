# LTI 1.3 Basic Outcome Library

> PHP library for [LTI 1.3 Basic Outcome](https://www.imsglobal.org/spec/lti-bo/v1p1) implementations as platforms and / or as tools, based on [LTI 1.3 Core library](https://github.com/oat-sa/lib-lti1p3-core).

# Table of contents

- [Specifications](#specifications)
- [Installation](#installation)
- [Tutorials](#tutorials)
- [Tests](#tests)

## Specifications

- [IMS LTI 1.3 Basic Outcome Service Integration](https://www.imsglobal.org/spec/lti-bo/v1p1#integration-with-lti-1-3)
- [IMS LTI 1.3 Core](http://www.imsglobal.org/spec/lti/v1p3)
- [IMS Security](https://www.imsglobal.org/spec/security/v1p0)

## Installation

```console
$ composer require oat-sa/lib-lti1p3-basic-outcome
```

## Tutorials

You can then find below usage tutorials, presented by topics.

### Configuration

- how to [configure the underlying LTI 1.3 Core library](https://github.com/oat-sa/lib-lti1p3-core#quick-start)

### Tool

- how to [use the Basic Outcome library as a tool](doc/tool.md)

### Platform

- how to [use the Basic Outcome library as a platform](doc/platform.md)

## Tests

To run tests:

```console
$ vendor/bin/phpunit
```
**Note**: see [phpunit.xml.dist](phpunit.xml.dist) for available test suites.