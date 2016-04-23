<?php

use Halaei\HRedis\RedisJob;
use Halaei\HRedis\RedisQueue;
use Mockery as m;

class RedisJobTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testFireProperlyCallsTheJobHandler()
    {
        $job = $this->getJob();
        $job->getContainer()->shouldReceive('make')->once()->with('foo')->andReturn($handler = m::mock('StdClass'));
        $handler->shouldReceive('fire')->once()->with($job, ['data']);

        $job->fire();
    }

    public function testDeleteRemovesTheJobFromRedis()
    {
        $job = $this->getJob();
        $job->getRedisQueue()->shouldReceive('deleteReserved')->once()
            ->with('default', json_encode(['job' => 'foo', 'data' => ['data'], 'attempts' => 2]));

        $job->delete();
    }

    public function testReleaseProperlyReleasesJobOntoRedis()
    {
        $job = $this->getJob();
        $job->getRedisQueue()->shouldReceive('deleteAndRelease')->once()
            ->with('default', json_encode(['job' => 'foo', 'data' => ['data'], 'attempts' => 2]), 1);

        $job->release(1);
    }

    protected function getJob()
    {
        return new RedisJob(
            m::mock(Illuminate\Container\Container::class),
            m::mock(RedisQueue::class),
            json_encode(['job' => 'foo', 'data' => ['data'], 'attempts' => 1]),
            json_encode(['job' => 'foo', 'data' => ['data'], 'attempts' => 2]),
            'default'
        );
    }
}
