<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Transformers\ErrorResource;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register(): void
    {
        $this->renderable(function (AuthenticationException $e, $request) {
            return $this->makeErrorResponse(401, $e->getMessage(), null);
        });

        $this->renderable(function (ApiException $e, $request) {
            return $this->makeErrorResponse($e->getCode(), $e->getMessage(), null, $e->getData());
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            return $this->makeErrorResponse(404, $e->getMessage(), null);
        });

        $this->renderable(function (UnauthorizedException $e, $request) {
            return $this->makeErrorResponse(403, $e->getMessage(), null);
        });

        $this->renderable(function (ValidationException $e, $request) {
            $errors = $e->errors();
            return $this->makeErrorResponse(422, __('The given data was invalid'), $errors);
        });
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param Request $request
     * @param ValidationException $e
     * @return Response
     */
    protected function invalidJson($request, ValidationException $e): Response
    {
        return $this->makeErrorResponse($e->status, $e->getMessage(), $e->errors());
    }

    /**
     * Prepare a JSON response for the given exception.
     *
     * @param  Request  $request
     * @param  \Throwable  $e
     * @return Response
     */
    protected function prepareJsonResponse($request, Throwable $e): Response
    {
        if (config('app.debug')) {
            return $this->makeErrorResponse(500, trans('exceptions.server_error'), $this->convertExceptionToArray($e));
        }

        return $this->makeErrorResponse(500, trans('exceptions.server_error'));
    }

    /**
     * @param int $code
     * @param string $message
     * @param array|null $errors
     * @param mixed|null $data
     * @return Response
     */
    protected function makeErrorResponse(int $code, string $message, ?array $errors = null, $data = null): Response
    {
        return (new ErrorResource($code, $message, $errors, $data))->response()->setStatusCode($code);
    }
}
