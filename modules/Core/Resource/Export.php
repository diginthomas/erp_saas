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

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Contracts\Fields\Dateable;
use Modules\Core\Fields\Field;
use Modules\Core\Fields\FieldsCollection;
use Modules\Core\Resource\Exceptions\InvalidExportTypeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Export implements FromCollection, WithHeadings, WithMapping
{
    /**
     * The allowed export file types
     *
     * @var array
     */
    const ALLOWED_TYPES = [
        'csv' => \Maatwebsite\Excel\Excel::CSV,
        'xls' => \Maatwebsite\Excel\Excel::XLS,
        'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
    ];

    /**
     * Default export type
     *
     * @var string
     */
    const DEFAULT_TYPE = 'csv';

    /**
     * Export chunk size.
     */
    public static int $chunkSize = 500;

    /**
     * The fields that are available for export.
     */
    public FieldsCollection $availableFields;

    /**
     * Create new Export instance.
     */
    public function __construct(protected Resource $resource, protected Builder $query)
    {
        $this->availableFields = $resource->fieldsForExport();
    }

    /**
     * Map the export rows
     *
     * @param  \Modules\Core\Models\Model  $model
     */
    public function map($model): array
    {
        return $this->getFields()->map(
            fn (Field $field) => $field->resolveForExport($model)
        )->all();
    }

    /**
     * Provides the export eadings.
     */
    public function headings(): array
    {
        return $this->getFields()->map(
            fn (Field $field) => $this->heading($field)
        )->all();
    }

    /**
     * Create heading for export for the given field.
     */
    public function heading(Field $field): string
    {
        if ($field instanceof Dateable) {
            return $field->label.' ('.config('app.timezone').')';
        }

        return $field->label;
    }

    /**
     * Set fields to be used when exporting.
     */
    public function setFields(FieldsCollection $fields): static
    {
        $this->availableFields = $fields;

        return $this;
    }

    /**
     * Get the fields for the export.
     */
    public function getFields(): FieldsCollection
    {
        return $this->availableFields;
    }

    /**
     * Perform and download the export
     */
    public function download(?string $type = null): BinaryFileResponse
    {
        return Excel::download(
            $this,
            $this->fileName().'.'.$type,
            $this->determineType($type)
        );
    }

    /**
     * Provides the export data.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->query->lazy(static::$chunkSize);
    }

    /**
     * The export file name (without extension)
     */
    public function fileName(): string
    {
        return $this->resource->name();
    }

    /**
     * Determine the type.
     *
     * @throws \Modules\Core\Resource\Exceptions\InvalidExportTypeException
     */
    protected function determineType(?string $type): string
    {
        if (is_null($type)) {
            return static::ALLOWED_TYPES[static::DEFAULT_TYPE];
        } elseif (! array_key_exists($type, static::ALLOWED_TYPES)) {
            throw new InvalidExportTypeException($type);
        }

        return static::ALLOWED_TYPES[$type];
    }
}
