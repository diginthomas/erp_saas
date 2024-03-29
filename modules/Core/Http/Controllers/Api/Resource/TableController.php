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

namespace Modules\Core\Http\Controllers\Api\Resource;

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\ApiController;
use Modules\Core\Http\Requests\ResourceTableRequest;
use Modules\Core\Http\Resources\TableResource;
use Modules\Core\QueryBuilder\Exceptions\QueryBuilderException;

class TableController extends ApiController
{
    /**
     * Display a table listing of the resource.
     */
    public function index(ResourceTableRequest $request): JsonResponse
    {
        try {
            $table = $request->boolean('trashed') ?
                $request->resolveTrashedTable() :
                $request->resolveTable();

            return $this->response(
                TableResource::collection($table->make())->additional(['meta' => array_merge([
                    'pre_total' => $table->preTotal,
                ], $table->meta)])
            );
        } catch (QueryBuilderException $e) {
            abort(400, $e->getMessage());
        }
    }

    /**
     * Get the resource table settings.
     */
    public function settings(ResourceTableRequest $request): JsonResponse
    {
        return $this->response(
            $request->boolean('trashed') ?
            $request->resolveTrashedTable()->settings() :
            $request->resolveTable()->settings()
        );
    }

    /**
     * Customize the resource table.
     */
    public function customize(ResourceTableRequest $request): JsonResponse
    {
        $table = tap($request->resolveTable(), function ($table) {
            abort_unless($table->customizeable, 403, 'This table cannot be customized.');
        });

        return $this->response(
            $table->settings()->update($request->all())
        );
    }
}
