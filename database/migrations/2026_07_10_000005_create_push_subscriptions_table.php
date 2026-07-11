<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('endpoint');
            $table->string('public_key')->nullable();
            $table->string('auth_token')->nullable();
            $table->string('encoding')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE push_subscriptions ADD UNIQUE INDEX push_endpoint_unique (endpoint(191))');
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
