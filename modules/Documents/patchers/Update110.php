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

use Modules\Core\Updater\UpdatePatcher;
use Modules\Documents\Models\DocumentType;

return new class extends UpdatePatcher
{
    public function run(): void
    {
        if ($this->missingDocumentTypes()) {
            $this->createDefaultDocumentTypes();
        }
    }

    public function shouldRun(): bool
    {
        return $this->missingDocumentTypes();
    }

    protected function createDefaultDocumentTypes(): void
    {
        foreach ([
            'Proposal' => '#a3e635',
            'Quote' => '#64748b',
            'Contract' => '#ffd600',
        ] as $name => $color) {
            $model = new DocumentType;
            $model->forceFill([
                'name' => $name,
                'swatch_color' => $color,
                'flag' => strtolower($name),
            ])->save();

            if ($model->flag == 'proposal') {
                DocumentType::setDefault($model->getKey());
            }
        }
    }

    protected function missingDocumentTypes(): bool
    {
        return DocumentType::count() === 0;
    }
};
