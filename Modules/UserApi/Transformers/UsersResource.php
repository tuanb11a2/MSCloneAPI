<?php

namespace Modules\UserApi\Transformers;

use Illuminate\Http\Resources\Json\ResourceCollection;


class UsersResource extends ResourceCollection
{
    /**
     * @var array
     */
    private array $pagination;

    public function __construct($resource)
    {
        $this->pagination = [
            'total' => $resource->total(),
            'count' => $resource->count(),
            'per_page' => $resource->perPage(),
            'current_page' => $resource->currentPage(),
            'total_pages' => $resource->lastPage()
        ];

        $resource = $resource->getCollection();

        parent::__construct($resource);
    }


    /**
     * Transform the resource into an array.
     *
     * @param  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'data' => UserResource::collection($this->collection),
            'meta' => [
                'code' => 200,
                'message' => 'Successful',
                'pagination' => $this->pagination,
            ],
        ];
    }
}
