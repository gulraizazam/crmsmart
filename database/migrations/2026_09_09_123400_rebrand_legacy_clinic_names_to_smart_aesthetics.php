<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('accounts')->orderBy('id')->get() as $account) {
            $name = $this->rebrand((string) $account->name);
            if ($name !== $account->name) {
                DB::table('accounts')->where('id', $account->id)->update(['name' => $name]);
            }
        }

        foreach (DB::table('locations')->orderBy('id')->get() as $location) {
            $name = $this->rebrand((string) $location->name);
            if ($name !== $location->name) {
                DB::table('locations')->where('id', $location->id)->update(['name' => $name]);
            }
        }

        foreach (DB::table('sms_templates')->orderBy('id')->get() as $template) {
            $content = $this->rebrand((string) $template->content);
            if ($content !== $template->content) {
                DB::table('sms_templates')->where('id', $template->id)->update([
                    'content' => $content,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Branding rename is not reversed; names may have been edited by users after this ran.
    }

    private function rebrand(string $text): string
    {
        $text = preg_replace('/\bCutera Aesthetics\b/i', 'Smart Aesthetics', $text) ?? $text;
        $text = preg_replace('/\bCUTERA\b/i', 'Smart Aesthetics', $text) ?? $text;
        $text = preg_replace('/\bCutera\b/i', 'Smart Aesthetics', $text) ?? $text;
        $text = preg_replace('/\bRED\s+SIGNAL\b/i', 'Smart Aesthetics', $text) ?? $text;
        $text = preg_replace('/\bRedSignal\b/i', 'Smart Aesthetics', $text) ?? $text;
        $text = preg_replace('/\bredsignal\b/i', 'Smart Aesthetics', $text) ?? $text;

        return $text;
    }
};
