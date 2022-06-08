<?php

namespace App\Transformers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class Resource extends JsonResource
{
    /**
     * @var $meta
     */
    protected $meta;

    public static $wrap = 'data';

    /**
     * Resource constructor.
     * @param $resource
     * @param MetaResource|MetaPaginationResource $meta
     */
    public function __construct($resource, $meta)
    {
        $this->meta = $meta;
        parent::__construct($resource);
    }

    public function getMeta() {
        return $this->meta;
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param Request $request
     * @return array
     */
    public function with($request): array
    {
        return [
            'meta' => $this->meta->toArray($request),
        ];
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function toResponse($request): JsonResponse
    {
        return (new ResourceResponse($this))->toResponse($request);
    }
}
