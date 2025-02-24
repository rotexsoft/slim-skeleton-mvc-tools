<?php

namespace SlimMvcTools;

/**
 *
 * @author rotimi
 */
enum SlimHttpExceptionClassNames: string {
    
    case HttpBadRequestException            = \Slim\Exception\HttpBadRequestException::class;
    case HttpForbiddenException             = \Slim\Exception\HttpForbiddenException::class;
    case HttpGoneException                  = \Slim\Exception\HttpGoneException::class;
    case HttpInternalServerErrorException   = \Slim\Exception\HttpInternalServerErrorException::class;
    case HttpMethodNotAllowedException      = \Slim\Exception\HttpMethodNotAllowedException::class;
    case HttpNotFoundException              = \Slim\Exception\HttpNotFoundException::class;
    case HttpNotImplementedException        = \Slim\Exception\HttpNotImplementedException::class;
    case HttpTooManyRequestsException       = \Slim\Exception\HttpTooManyRequestsException::class;
    case HttpUnauthorizedException          = \Slim\Exception\HttpUnauthorizedException::class;
}

