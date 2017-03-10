* Strive for 100% Unit Test Coverage
* Create custom email for project's composer.json
* Update travis link in README.md
* Look into using example in https://akrabat.com/testing-slim-framework-actions/ for writing unit tests for actions in the mvc-app package.

Look at \Slim\Handlers\Error, \Slim\Handlers\NotAllowed and \Slim\Handlers\NotFound and see how 
\Slim3MvcTools\Controllers\BaseController::generateNotAllowedResponse(..) ,
\Slim3MvcTools\Controllers\BaseController::generateNotFoundResponse(..) and 
\Slim3MvcTools\Controllers\BaseController::generateServerErrorResponse(..) 
can be improved upon whilst enjoying the preAction() and postAction() benefits 
of the \Slim3MvcTools\Controllers\BaseController architecture. 
Biggest thing is to allow the handlers to return appropriate content-type 
for html, json and xml requests. Right now only html responses are being returned.

\Slim\Handlers::$knownContentTypes and
\Slim\Handlers::determineContentType(ServerRequestInterface $request) are of huge interest
 
Change BaseController::preAction() & BaseController::postAction() to 
preAction():\Psr\Http\Message\ResponseInterface $response
& postAction(\Psr\Http\Message\ResponseInterface $response):\Psr\Http\Message\ResponseInterface $response
