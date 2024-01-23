<?php

namespace Tests\Fixtures;

use Modules\Core\Filters\Text;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Table\Table;

class EventTable extends Table
{
    public function __construct($query = null, $request = null)
    {
        parent::__construct(
            $query ?: (new Event)->newQuery(),
            $request ?: app(ResourceRequest::class)
        );
    }

    public function columns(): array
    {
        return [
            // TODO
        ];
    }

    public function filters(ResourceRequest $request): array
    {
        return [
            Text::make('title', 'Title'),
            Text::make('description', 'Description')->canSee(function () {
                return false;
            }),
        ];
    }
}
