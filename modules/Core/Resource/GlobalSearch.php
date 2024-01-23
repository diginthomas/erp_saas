<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.3.5
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2024 KONKORD DIGITAL
 */

namespace Modules\Core\Resource;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use JsonSerializable;
use Modules\Core\Contracts\Presentable;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Model;

class GlobalSearch implements JsonSerializable
{
    /**
     * Initialize global search for the given resources.
     *
     * @param  \Modules\Core\Resource\Resource[]  $resources
     */
    public function __construct(protected ResourceRequest $request, protected array $resources)
    {
    }

    /**
     * Get the search result.
     */
    public function get(): Collection
    {
        $result = new Collection([]);

        foreach ($this->resources as $resource) {
            if (count($resource->globalSearchColumns()) === 0) {
                continue;
            }

            $result->push([
                'title' => $resource->label(),
                'data' => $this->newQuery($resource)
                    ->take($resource->globalSearchResultsLimit)
                    ->get()
                    ->whereInstanceOf(Presentable::class)
                    ->map(function (Model&Presentable $model) use ($resource) {
                        return $this->data($model, $resource);
                    }),
            ]);
        }

        return $result->reject(
            fn ($result) => $result['data']->isEmpty()
        )->values();
    }

    /**
     * Get the query that should be used to perform global search.
     */
    protected function newQuery(Resource $resource): Builder
    {
        return $resource->globalSearchQuery($this->request);
    }

    /**
     * Get the model data for the response.
     */
    protected function data(Model&Presentable $model, Resource $resource): array
    {
        return [
            'id' => $model->getKey(),
            'path' => $model->path,
            'display_name' => $model->display_name,
            'created_at' => $model->created_at,
            'resourceName' => $resource->name(),
        ];
    }

    /**
     * Serialize GlobalSearch class.
     */
    public function jsonSerialize(): array
    {
        return $this->get()->all();
    }
}
