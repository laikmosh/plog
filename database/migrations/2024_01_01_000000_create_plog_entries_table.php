<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $connection = config('plog.database.connection', 'plog');

        if (!Schema::connection($connection)->hasTable('plog_entries')) {
            Schema::connection($connection)->create('plog_entries', function (Blueprint $table) {
                $table->id();
                $table->string('level', 20)->index();
                $table->text('message');
                $table->json('context')->nullable();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('session_id', 255)->nullable()->index();
                $table->string('request_id', 36)->nullable()->index();
                $table->string('environment', 20)->nullable()->index();
                $table->string('endpoint', 500)->nullable()->index();
                $table->string('file', 500)->nullable();
                $table->integer('line')->nullable();
                $table->string('class', 255)->nullable()->index();
                $table->string('method', 255)->nullable();
                $table->json('tags')->nullable();
                $table->string('retention_group', 100)->nullable()->index();
                $table->timestamp('created_at')->useCurrent()->index();

                $table->index(['created_at', 'level']);
                $table->index(['request_id', 'created_at']);
                $table->index(['user_id', 'created_at']);

                // GIN index is PostgreSQL specific, skip for SQLite
                if (config('database.connections.' . config('plog.database.connection', 'plog') . '.driver') === 'pgsql') {
                    $table->index('tags', 'plog_entries_tags_gin')->algorithm('gin');
                }
            });
        }
    }

    public function down()
    {
        $connection = config('plog.database.connection', 'plog');
        Schema::connection($connection)->dropIfExists('plog_entries');
    }
};