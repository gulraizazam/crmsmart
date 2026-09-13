<?php

namespace App\Console\Commands;

use App\Services\Meta\MetaLeadService;
use Illuminate\Console\Command;

class CatchUpMetaLeads extends Command
{
    protected $signature = 'meta:catch-up {--account=}';

    protected $description = 'Fetch recent Meta Lead Ads and ingest any leadgen ids the webhook missed';

    public function handle(MetaLeadService $metaLeadService): int
    {
        $accountId = $this->option('account') ? (int) $this->option('account') : null;
        $stats = $metaLeadService->catchUp($accountId);
        $this->info('Forms: ' . $stats['forms'] . ', seen: ' . $stats['seen'] . ', processed: ' . $stats['queued']);

        return 0;
    }
}
