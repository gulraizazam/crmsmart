<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('meta_lead_settings')) {
            Schema::create('meta_lead_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('account_id')->unique();
                $table->string('page_id')->nullable();
                $table->string('page_name')->nullable();
                $table->text('access_token')->nullable();
                $table->string('verify_token')->nullable();
                $table->text('app_secret')->nullable();
                $table->unsignedBigInteger('lead_source_id')->nullable();
                $table->unsignedBigInteger('lead_status_id')->nullable();
                $table->unsignedBigInteger('city_id')->nullable();
                $table->unsignedBigInteger('location_id')->nullable();
                $table->unsignedBigInteger('department_id')->nullable();
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->unsignedTinyInteger('gender')->nullable();
                $table->boolean('active')->default(0);
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('meta_lead_events')) {
            Schema::create('meta_lead_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('account_id')->nullable();
                $table->string('leadgen_id')->nullable()->unique();
                $table->string('page_id')->nullable();
                $table->string('form_id')->nullable();
                $table->unsignedBigInteger('lead_id')->nullable();
                $table->string('status', 32)->default('received')->index();
                $table->text('error')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('leads') && Schema::hasColumn('leads', 'meta_lead_id')) {
            DB::table('leads')->where('meta_lead_id', '')->update(['meta_lead_id' => null]);
            $hasDupes = DB::table('leads')
                ->select('meta_lead_id')
                ->whereNotNull('meta_lead_id')
                ->where('meta_lead_id', '!=', '')
                ->groupBy('meta_lead_id')
                ->havingRaw('COUNT(*) > 1')
                ->exists();
            $indexExists = collect(DB::select("SHOW INDEX FROM leads WHERE Key_name = 'leads_meta_lead_id_unique'"))->isNotEmpty();
            if (! $hasDupes && ! $indexExists) {
                Schema::table('leads', function (Blueprint $table) {
                    $table->unique('meta_lead_id', 'leads_meta_lead_id_unique');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('leads')) {
            $indexExists = collect(DB::select("SHOW INDEX FROM leads WHERE Key_name = 'leads_meta_lead_id_unique'"))->isNotEmpty();
            if ($indexExists) {
                Schema::table('leads', function (Blueprint $table) {
                    $table->dropUnique('leads_meta_lead_id_unique');
                });
            }
        }
        Schema::dropIfExists('meta_lead_events');
        Schema::dropIfExists('meta_lead_settings');
    }
};
