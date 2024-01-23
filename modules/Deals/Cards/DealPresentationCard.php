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

namespace Modules\Deals\Cards;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use Modules\Core\Charts\ChartResult;
use Modules\Core\Charts\Presentation;
use Modules\Deals\Models\Pipeline;
use Modules\Deals\Models\Stage;

abstract class DealPresentationCard extends Presentation
{
    /**
     * Stages cache.
     */
    protected ?Collection $stages = null;

    /**
     * Add stages labels to the result.
     */
    protected function withStageLabels(ChartResult $result): ChartResult
    {
        return $result->label(
            fn ($value) => $this->stages()->find($value)->name
        );
    }

    /**
     * Get the deals pipeline for the card.
     */
    protected function getPipelineId(Request $request): int
    {
        return ! $request->filled('pipeline_id') ?
                Pipeline::visible()->userOrdered()->first()->getKey() :
                 $request->integer('pipeline_id');
    }

    /**
     * Get all available stages.
     */
    protected function stages(): Collection
    {
        return $this->stages ??= Stage::select(['id', 'name'])->get();
    }

    /**
     * The element's component.
     */
    public function component(): string
    {
        return 'deal-presentation-card';
    }

    /**
     * Get the cache key for the card.
     */
    public function getCacheKey(Request $request): string
    {
        return sprintf(
            parent::getCacheKey($request).'.%s',
            $this->getPipelineId($request),
        );
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'pipeline_id' => $this->getPipelineId(RequestFacade::instance()),
        ]);
    }
}
