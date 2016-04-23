<?php

namespace Halaei\HRedis;

use Illuminate\Support\Arr;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;

class RedisJob extends Job implements JobContract
{
    /**
     * The Redis queue instance.
     *
     * @var \Halaei\HRedis\RedisQueue
     */
    protected $redis;

    /**
     * The Redis job payload.
     *
     * @var string
     */
    protected $job;

    /**
     * The Redis job payload inside the reserved queue.
     *
     * @var string
     */
    protected $reserved;

    /**
     * Create a new job instance.
     *
     * @param  \Illuminate\Container\Container  $container
     * @param  RedisQueue  $redis
     * @param  string  $job
     * @param  string  $reserved
     * @param  string  $queue
     */
    public function __construct(Container $container, RedisQueue $redis, $job, $reserved, $queue)
    {
        $this->job = $job;
        $this->reserved = $reserved;
        $this->redis = $redis;
        $this->queue = $queue;
        $this->container = $container;
    }

    /**
     * Fire the job.
     *
     * @return void
     */
    public function fire()
    {
        $this->resolveAndFire(json_decode($this->getRawBody(), true));
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->job;
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        parent::delete();

        $this->redis->deleteReserved($this->queue, $this->reserved);
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int   $delay
     * @return void
     */
    public function release($delay = 0)
    {
        parent::release($delay);

        $this->redis->deleteAndRelease($this->queue, $this->reserved, $delay);
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        return Arr::get(json_decode($this->job, true), 'attempts');
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId()
    {
        return Arr::get(json_decode($this->job, true), 'id');
    }

    /**
     * Get the IoC container instance.
     *
     * @return \Illuminate\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get the underlying queue driver instance.
     *
     * @return \Illuminate\Redis\Database
     */
    public function getRedisQueue()
    {
        return $this->redis;
    }

    /**
     * Get the underlying Redis job.
     *
     * @return string
     */
    public function getRedisJob()
    {
        return $this->job;
    }

    /**
     * Get the underlying reserved Redis job.
     * @return string
     */
    public function getReservedJob()
    {
        return $this->reserved;
    }
}
