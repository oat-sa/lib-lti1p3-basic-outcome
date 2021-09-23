CHANGELOG
=========

4.1.1
-----

* Fixed wrong response http status code to return a bad request when the provided operation xml is invalid

4.1.0
-----

* Added methods to BasicOutcomeServiceClient to work with claim
* Updated documentation

4.0.0
-----

* Added github actions CI
* Removed jenkins and travis CI
* Updated oat-sa/lib-lti1p3-core dependency to version 6.0
* Updated documentation

3.0.0
-----

* Added psalm support
* Deleted BasicOutcomeServiceServer in favor to BasicOutcomeServiceServerRequestHandler (to be used with core LtiServiceServer)
* Updated oat-sa/lib-lti1p3-core dependency to version 5.0
* Updated BasicOutcomeServiceServerProcessorInterface methods parameters to work with registration
* Updated overall constructors to handle nullable parameters
* Updated documentation

2.0.0
-----

* Added PHP 8 support (and kept >=7.2)
* Updated oat-sa/lib-lti1p3-core dependency to version 4.0
* Removed library generator usage in favor of core's one
* Updated documentation

1.0.0
-----

* Added platform side of the library
* Reworked tool side and foundations of the library
* Upgraded for oat-sa/lib-lti1p3-core version 3.3
* Updated documentation

0.2.0
-----

* Upgraded for oat-sa/lib-lti1p3-core version 3.0.0
* Updated documentation

0.1.0
-----

* Provided Basic Outcome client
