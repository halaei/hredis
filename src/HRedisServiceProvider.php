<?php

namespace Halaei\HRedis;

use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;

class HRedisServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }

    public function boot(QueueManager $queueManager)
    {
        $queueManager->addConnector('hredis', function() use ($queueManager) {
            return new RedisConnector($this->app['redis']);
        });
    }
}
