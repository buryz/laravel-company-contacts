<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearAppCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear application specific cache keys';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Clear search cache
        $this->clearCacheByPattern('search_*');
        
        // Clear export cache
        $this->clearCacheByPattern('export_*');
        
        // Clear suggestions cache
        $this->clearCacheByPattern('suggestions_*');
        
        // Clear other specific cache keys
        Cache::forget('unique_companies');
        Cache::forget('unique_positions');
        Cache::forget('available_tags');
        
        $this->info('Application cache cleared successfully.');
        
        return Command::SUCCESS;
    }
    
    /**
     * Clear cache by pattern (works with Redis and other drivers that support pattern deletion)
     */
    private function clearCacheByPattern(string $pattern)
    {
        // For Redis cache driver
        if (config('cache.default') === 'redis') {
            $redis = Cache::getRedis();
            $keys = $redis->keys(config('cache.prefix') . ':' . $pattern);
            
            foreach ($keys as $key) {
                // Extract the cache key without prefix
                $cacheKey = str_replace(config('cache.prefix') . ':', '', $key);
                Cache::forget($cacheKey);
            }
        } else {
            // For other cache drivers, we can't easily clear by pattern
            // Just log that this feature requires Redis
            $this->warn("Pattern-based cache clearing requires Redis cache driver. Using '{$pattern}' pattern.");
        }
    }
}