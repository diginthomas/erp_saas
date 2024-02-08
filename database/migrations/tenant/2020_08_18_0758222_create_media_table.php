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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('disk')->nullable();
            $table->string('directory')->nullable();
            $table->string('extension')->nullable();
            $table->string('filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('aggregate_type')->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->string('variant_name')->nullable();
            $table->unsignedInteger('original_media_id')->nullable();
            // $table->foreignId('original_media_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @codeCoverageIgnore
     */
    public function down(): void
    {
        Schema::dropIfExists('phones');
    }
};
