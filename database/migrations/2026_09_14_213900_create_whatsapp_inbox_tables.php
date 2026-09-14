<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('whatsapp_settings')) {
            Schema::create('whatsapp_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('account_id')->unique();
                $table->string('phone_number_id', 64)->nullable();
                $table->string('waba_id', 64)->nullable();
                $table->string('display_phone', 32)->nullable();
                $table->text('access_token')->nullable();
                $table->string('verify_token', 150)->nullable();
                $table->text('app_secret')->nullable();
                $table->boolean('active')->default(0);
                $table->unsignedInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('whatsapp_conversations')) {
            Schema::create('whatsapp_conversations', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('account_id');
                $table->unsignedInteger('patient_id')->nullable();
                $table->string('phone', 32);
                $table->string('contact_name', 150)->nullable();
                $table->text('last_message_preview')->nullable();
                $table->string('last_message_direction', 16)->nullable();
                $table->timestamp('last_message_at')->nullable();
                $table->timestamp('last_inbound_at')->nullable();
                $table->unsignedInteger('unread_count')->default(0);
                $table->timestamps();

                $table->unique(['account_id', 'phone']);
                $table->index(['account_id', 'last_message_at']);
                $table->index(['account_id', 'patient_id']);
            });
        } else {
            Schema::table('whatsapp_conversations', function (Blueprint $table) {
                if (! Schema::hasColumn('whatsapp_conversations', 'account_id')) {
                    $table->unsignedInteger('account_id')->nullable()->after('id');
                }
                if (! Schema::hasColumn('whatsapp_conversations', 'phone')) {
                    $table->string('phone', 32)->nullable()->after('patient_id');
                }
                if (! Schema::hasColumn('whatsapp_conversations', 'contact_name')) {
                    $table->string('contact_name', 150)->nullable();
                }
                if (! Schema::hasColumn('whatsapp_conversations', 'last_message_preview')) {
                    $table->text('last_message_preview')->nullable();
                }
                if (! Schema::hasColumn('whatsapp_conversations', 'last_message_direction')) {
                    $table->string('last_message_direction', 16)->nullable();
                }
                if (! Schema::hasColumn('whatsapp_conversations', 'unread_count')) {
                    $table->unsignedInteger('unread_count')->default(0);
                }
            });
        }

        if (! Schema::hasTable('whatsapp_messages')) {
            Schema::create('whatsapp_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('conversation_id');
                $table->unsignedInteger('account_id');
                $table->string('direction', 16);
                $table->string('type', 32)->default('text');
                $table->text('body')->nullable();
                $table->string('wa_message_id', 128)->nullable();
                $table->string('status', 24)->default('pending');
                $table->text('error_message')->nullable();
                $table->unsignedInteger('sent_by')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();

                $table->index(['conversation_id', 'id']);
                $table->unique('wa_message_id');
            });
        } else {
            Schema::table('whatsapp_messages', function (Blueprint $table) {
                if (! Schema::hasColumn('whatsapp_messages', 'conversation_id')) {
                    $table->unsignedBigInteger('conversation_id')->nullable()->after('id');
                }
                if (! Schema::hasColumn('whatsapp_messages', 'account_id')) {
                    $table->unsignedInteger('account_id')->nullable()->after('conversation_id');
                }
                if (! Schema::hasColumn('whatsapp_messages', 'wa_message_id')) {
                    $table->string('wa_message_id', 128)->nullable();
                }
                if (! Schema::hasColumn('whatsapp_messages', 'error_message')) {
                    $table->text('error_message')->nullable();
                }
                if (! Schema::hasColumn('whatsapp_messages', 'sent_by')) {
                    $table->unsignedInteger('sent_by')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        // Leave existing WhatsApp tables in place; they predate this inbox.
    }
};
