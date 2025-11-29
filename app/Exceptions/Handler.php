<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [];

    protected $dontReport = [];

    protected $dontFlash = ['password', 'password_confirmation'];

    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson()
            ? response()->json(['message' => 'Unauthenticated.'], 401)
            : redirect()->route('login');
    }

    public function render($request, Throwable $exception)
    {
        // Determine HTTP Status Code and Message
        $statusCode = 500;
        $message = 'An unexpected error occurred.';

        switch (true) {

            case $exception instanceof TokenExpiredException:
                $statusCode = 401;
                $message = 'Token has expired.';
                break;

            case $exception instanceof TokenInvalidException:
                $statusCode = 401;
                $message = 'Token is invalid.';
                break;

            case $exception instanceof JWTException:
                $statusCode = 401;
                $message = 'Token is missing or could not be parsed.';
                break;

            case $exception instanceof AuthenticationException:
                $statusCode = 401;
                $message = 'Authentication required.';
                break;

            case $exception instanceof ValidationException:
                $statusCode = 422;
                $message = 'Validation failed.';
                $errors = $exception->errors(); // Provide specific validation errors
                break;

            case $exception instanceof ModelNotFoundException:
                $statusCode = 404;
                $message = 'Resource not found.';
                break;

            case $exception instanceof NotFoundHttpException:
                $statusCode = 404;
                $message = 'The requested endpoint was not found.';
                break;

            case $exception instanceof MethodNotAllowedHttpException:
                $statusCode = 405;
                $message = 'HTTP method not allowed.';
                break;

            case $exception instanceof HttpException:
                $statusCode = $exception->getStatusCode();
                $message = $exception->getMessage() ?: 'HTTP error occurred.';
                break;

            case $exception instanceof AuthorizationException:
                    $statusCode = 403;
                    $message = $exception->getMessage() ?: 'HTTP error occurred.';
                    break;

            case $exception instanceof RouteNotFoundException:
                // Check if the exception was due to a missing `login` route
                if (str_contains($exception->getMessage(), '[login]')) {
                    $statusCode = 401;
                    $message = 'Invalid login token.';
                } else {
                    // Handle as a 404 if not related to `login`
                    $statusCode = 404;
                    $message = 'Route not found.';
                }
                break;

            default:
                // Default to 500 for unexpected exceptions
                $statusCode = $exception->getCode() ?: 500;
                $message = 'Oops. We are not able handle your request at this time. Please contact support if the issue persists for too long.'; // $exception->getMessage() ?: 'Server Error';
                break;
        }

        // Ensure status code is valid
        if (!in_array($statusCode, [400, 401, 403, 404, 405, 422, 500], true)) {
            $statusCode = 500;
        }

        // Build Response
        $response = [
            'success' => false,
            'message' => $message,
            'code' => $statusCode
        ];

        // Include validation errors, if available
        if (isset($errors)) {
            $response['errors'] = $errors;
        }

        // Additional debug info in development
        // if (config('app.debug')) {
        //     $response['trace'] = $exception->getTrace();
        //     $response['exception'] = get_class($exception);
        // }

        return response()->json($response, $statusCode);
    }
}
