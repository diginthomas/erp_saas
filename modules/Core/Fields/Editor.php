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

namespace Modules\Core\Fields;

use Modules\Core\Contracts\Fields\Deleteable;
use Modules\Core\Fields\Deleteable as DeleteableTrait;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Model;
use Modules\Core\Support\Media\EditorPendingMediaProcessor;
use Modules\Core\Support\Placeholders\GenericPlaceholder;
use Modules\Users\Mention\PendingMention;

class Editor extends Field implements Deleteable
{
    use DeleteableTrait;

    /**
     * Field component.
     */
    public static $component = 'editor-field';

    /**
     * The inline edit popover width (medium|large).
     */
    public string $inlineEditPanelWidth = 'large';

    /**
     * Initialize new Editor instance.
     */
    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this
            ->deleteUsing(function (Model $model) {
                if ($model->isReallyDeleting()) {
                    $this->createImagesProcessor()->deleteAllViaModel(
                        $model,
                        $this->attribute
                    );
                }
            })
            ->fillUsing(function (Model $model, string $attribute, ResourceRequest $request, ?string $value) {
                $mention = new PendingMention($value ?: '');

                if ($mention->hasMentions()) {
                    $value = $mention->getUpdatedText();
                }

                $model->{$attribute} = $value;

                return function () use ($mention, $model, $request) {
                    $this->runImagesProcessor($model);

                    $intermediate = $request->viaResource() ?
                        $request->findResource($request->via_resource)->newQuery()->find($request->via_resource_id) :
                        $model;

                    $mention->setUrl($intermediate->path)->withUrlQueryParameter([
                        'section' => $request->viaResource() ? $model->resource()->name() : null,
                        'resourceId' => $request->viaResource() ? $model->getKey() : null,
                    ])->notify();
                };
            })
            ->resolveUsing(fn ($model, $attribute) => clean($model->{$attribute}));
    }

    /**
     * Get the mailable template placeholder
     *
     * @param  \Modules\Core\Models\Model|null  $model
     * @return \Modules\Core\Support\Placeholders\GenericPlaceholder
     */
    public function mailableTemplatePlaceholder($model)
    {
        return GenericPlaceholder::make($this->attribute)
            ->description($this->label)
            ->withStartInterpolation('{{{')
            ->withEndInterpolation('}}}')
            ->value(function () use ($model) {
                return $this->resolveForDisplay($model);
            });
    }

    /**
     * Add mention support to the editor.
     */
    public function withMentions(): static
    {
        $this->withMeta([
            'attributes' => [
                'with-mention' => true,
            ],
        ]);

        return $this;
    }

    /**
     * Prepare the field when it's intended to be used on the bulk edit action.
     */
    public function prepareForBulkEdit(): void
    {
        unset($this->meta['attributes']['with-mention']);
        parent::prepareForBulkEdit();
    }

    /**
     * Run the editor images processor
     *
     * @param  $this  $model
     * @return void
     */
    protected function runImagesProcessor($model)
    {
        $this->createImagesProcessor()->processViaModel(
            $model,
            $this->attribute
        );
    }

    /**
     * Create editor images processor
     *
     * @return \Modules\Core\Support\Media\EditorPendingMediaProcessor
     */
    protected function createImagesProcessor()
    {
        return new EditorPendingMediaProcessor();
    }
}
