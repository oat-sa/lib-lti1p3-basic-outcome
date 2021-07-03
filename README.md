# <img src="doc/images/logo/logo.png" width="40" height="40"> [TAO](https://www.taotesting.com/) - LTI 1.3 Basic Outcome Library

[![Latest Version](https://img.shields.io/github/tag/oat-sa/lib-lti1p3-basic-outcome.svg?style=flat&label=release)](https://github.com/oat-sa/lib-lti1p3-basic-outcome/tags)
[![License GPL2](http://img.shields.io/badge/licence-GPL%202.0-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![Build Status](https://github.com/oat-sa/lib-lti1p3-basic-outcome/actions/workflows/build.yaml/badge.svg?branch=master)](https://github.com/oat-sa/lib-lti1p3-basic-outcome/actions)
[![Test Coverage Status](https://coveralls.io/repos/github/oat-sa/lib-lti1p3-basic-outcome/badge.svg?branch=master)](https://coveralls.io/github/oat-sa/lib-lti1p3-basic-outcome?branch=master)
[![Psalm Level Status](https://shepherd.dev/github/oat-sa/lib-lti1p3-basic-outcome/level.svg)](https://shepherd.dev/github/oat-sa/lib-lti1p3-basic-outcome)
[![Packagist Downloads](http://img.shields.io/packagist/dt/oat-sa/lib-lti1p3-basic-outcome.svg)](https://packagist.org/packages/oat-sa/lib-lti1p3-basic-outcome)

> PHP library for [LTI 1.3 Basic Outcome](https://www.imsglobal.org/spec/lti-bo/v1p1) implementations as [platforms and / or as tools](http://www.imsglobal.org/spec/lti/v1p3/#platforms-and-tools), based on [LTI 1.3 Core library](https://github.com/oat-sa/lib-lti1p3-core).

# Table of contents

- [TAO LTI 1.3 PHP framework](#tao-lti-13-php-framework)
- [IMS](#ims)
- [Installation](#installation)
- [Documentation](#documentation)
- [Tests](#tests)

## TAO LTI 1.3 PHP framework

This library is part of the [TAO LTI 1.3 PHP framework](https://oat-sa.github.io/doc-lti1p3/).

## IMS

You can find below [IMS](https://www.imsglobal.org/) related information.

### Related specifications

- [IMS LTI 1.3 Basic Outcome Service](https://www.imsglobal.org/spec/lti-bo/v1p1)
- [IMS LTI 1.3 Core](http://www.imsglobal.org/spec/lti/v1p3)
- [IMS Security](https://www.imsglobal.org/spec/security/v1p0)

## Installation

```console
$ composer require oat-sa/lib-lti1p3-basic-outcome
```

## Documentation

You can find below the library documentation, presented by topics.

### Configuration

- how to [configure the underlying LTI 1.3 Core library](https://github.com/oat-sa/lib-lti1p3-core#quick-start)

### Platform

- how to [use the basic outcome library as a platform](doc/platform.md)

### Tool

- how to [use the basic outcome library as a tool](doc/tool.md)

## Tests

To run tests:

```console
$ vendor/bin/phpunit
```
**Note**: see [phpunit.xml.dist](phpunit.xml.dist) for available test suites.