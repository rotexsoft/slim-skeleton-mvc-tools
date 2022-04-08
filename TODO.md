Add them to https://github.com/rotexsoft/slim-skeleton-mvc-tools/issues moving forward. 

* For a next major version, look into breaking actions apart e.g. actionShowLogin for GET requests to show login form and actionDoLogin for POST requests to do the login rather than have actionLogin handle both GET and POST requests.
* Strive for 100% Unit Test Coverage
* Switch dependency on **`container-interop/container-interop`** to the new PSR-11 container interfaces PSR and update all code reference and documentation
* Look into using example in https://akrabat.com/testing-slim-framework-actions/ for writing unit tests for actions in the mvc-app package.

```
Look at \Slim\Handlers\Error, \Slim\Handlers\NotAllowed and \Slim\Handlers\NotFound and see how 
\SlimMvcTools\Controllers\BaseController::generateNotAllowedResponse(..) ,
\SlimMvcTools\Controllers\BaseController::generateNotFoundResponse(..) and 
\SlimMvcTools\Controllers\BaseController::generateServerErrorResponse(..) 
can be improved upon whilst enjoying the preAction() and postAction() benefits 
of the \SlimMvcTools\Controllers\BaseController architecture. 
Biggest thing is to allow the handlers to return appropriate content-type 
for html, json and xml requests. Right now only html responses are being returned.

\Slim\Handlers::$knownContentTypes and
\Slim\Handlers::determineContentType(ServerRequestInterface $request) are of huge interest
```
