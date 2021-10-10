<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

class CleanStaleTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:clean-stale-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove stale Personal Access Tokens';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!config('sanctum.stale'))
            return;
        PersonalAccessToken::query()
            ->whereDate('last_used_at', '<', now()->subDays(config('sanctum.stale')))
            ->delete();
    }
}
