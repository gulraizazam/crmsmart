<?php

namespace App\Jobs;

use App\Services\Meta\MetaLeadService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMetaLeadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $eventId)
    {
        $this->queue = 'default';
    }

    public function handle(MetaLeadService $metaLeadService): void
    {
        $metaLeadService->processEvent($this->eventId);
    }
}
