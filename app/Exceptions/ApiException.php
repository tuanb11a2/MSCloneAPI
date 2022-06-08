<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class ApiException extends RuntimeException
{
    /** @var mixed|null  */
    private $data;

    /**
     * ApiException constructor.
     *
     * @param int $httpCode
     * @param string $message
     * @param null $data
     * @param Throwable|null $previous
     */
    public function __construct(int $httpCode, string $message, $data = null, Throwable $previous = null)
    {
        $this->data = $data;
        parent::__construct($message, $httpCode, $previous);
    }

    /**
     * @return mixed|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $message
     * @param null $data
     * @return ApiException
     */
    public static function serviceUnavailable(string $message = 'Service unavailable', $data = null): ApiException
    {
        return new ApiException(503, $message, $data);
    }

    /**
     * @param string $message
     * @param null $data
     * @return ApiException
     */
    public static function badRequest(string $message = "Bad request", $data = null): ApiException
    {
        return new ApiException(400, $message, $data);
    }

    /**
     * @param string $message
     * @param null $data
     * @return ApiException
     */
    public static function unauthorized(string $message = "Unauthorized", $data = null): ApiException
    {
        return new ApiException(401, $message, $data);
    }

    /**
     * @param string $message
     * @param null $data
     * @return ApiException
     */
    public static function forbidden(string $message = "forbidden", $data = null): ApiException
    {
        return new ApiException(403, $message, $data);
    }

    /**
     * @param string $message
     * @param null $data
     * @return ApiException
     */
    public static function notFound(string $message = "Not found", $data = null): ApiException
    {
        return new ApiException(404, $message, $data);
    }

    /**
     * @param string $message
     * @param null $data
     * @return ApiException
     */
    public static function conflict(string $message = "Conflict", $data = null): ApiException
    {
        return new ApiException(409, $message, $data);
    }
}
