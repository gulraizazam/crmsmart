<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('whatsapp_messages')) {
            return;
        }

        Schema::table('whatsapp_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('whatsapp_messages', 'media_path')) {
                $table->string('media_path', 255)->nullable();
            }
            if (! Schema::hasColumn('whatsapp_messages', 'mime_type')) {
                $table->string('mime_type', 120)->nullable();
            }
            if (! Schema::hasColumn('whatsapp_messages', 'file_name')) {
                $table->string('file_name', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        // Keep media columns; older inbox rows may already use them.
    }
};
