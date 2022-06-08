<?php

namespace App\Transformers;

use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     properties={
 *          @OA\Property(
 *              property="meta",
 *              ref="#/components/schemas/MetaResource"
 *          ),
 *     }
 * )
 */
class ErrorResource extends Resource
{
    public function __construct(int $code, string $message = 'Bad request', $errors = null, $resource = null)
    {
        parent::__construct($resource, new MetaResource($code, $message, $errors));
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
