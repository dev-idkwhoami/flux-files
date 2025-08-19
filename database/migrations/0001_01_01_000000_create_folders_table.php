<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->fluxFilesId();

            $table->string('name');
            $table->string('path');
            $table->nullableFluxFilesForeignId('parent_id');
            if (config('flux-files.tenancy.enabled', false) === true) {
                $table->nullableFluxFilesForeignId('tenant_id');
                $table->index(['tenant_id']);
            }
            $table->timestamps();

            $table->index(['parent_id']);
            $table->index(['path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
