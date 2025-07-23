<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->fluxFilesId();

            $table->string('name');
            $table->string('original_name');
            $table->string('path');
            $table->string('disk');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->fluxFilesForeignId('folder_id');
            if (config('flux-files.tenancy.enabled', false) === true) {
                $table->nullableFluxFilesForeignId('tenant_id');
                $table->index(['tenant_id']);
            }
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['folder_id']);
            $table->index(['disk']);
            $table->index(['mime_type']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
