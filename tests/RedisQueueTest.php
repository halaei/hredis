<?php

use Halaei\HRedis\RedisQueue;
use Mockery as m;

class RedisQueueTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testPushProperlyPushesJobOntoRedis()
    {
        $queue = $this->getMock(RedisQueue::class, ['getRandomId'], [$redis = m::mock('Illuminate\Redis\Database'), 'default']);
        $queue->expects($this->once())->method('getRandomId')->will($this->returnValue('foo'));
        $redis->shouldReceive('connection')->once()->andReturn($redis);
        $redis->shouldReceive('rpush')->once()->with('queues:default', json_encode(['job' => 'foo', 'data' => ['data'], 'id' => 'foo', 'attempts' => 1]));

        $id = $queue->push('foo', ['data']);
        $this->assertEquals('foo', $id);
    }

    public function testDelayedPushProperlyPushesJobOntoRedis()
    {
        $queue = $this->getMock(RedisQueue::class, ['getSeconds', 'getTime', 'getRandomId'], [$redis = m::mock('Illuminate\Redis\Database'), 'default']);
        $queue->expects($this->once())->method('getRandomId')->will($this->returnValue('foo'));
        $queue->expects($this->once())->method('getSeconds')->with(1)->will($this->returnValue(1));
        $queue->expects($this->once())->method('getTime')->will($this->returnValue(1));

        $redis->shouldReceive('connection')->once()->andReturn($redis);
        $redis->shouldReceive('zadd')->once()->with(
            'queues:default:delayed',
            2,
            json_encode(['job' => 'foo', 'data' => ['data'], 'id' => 'foo', 'attempts' => 1])
        );

        $id = $queue->later(1, 'foo', ['data']);
        $this->assertEquals('foo', $id);
    }

    public function testDelayedPushWithDateTimeProperlyPushesJobOntoRedis()
    {
        $date = Carbon\Carbon::now();
        $queue = $this->getMock(RedisQueue::class, ['getSeconds', 'getTime', 'getRandomId'], [$redis = m::mock('Illuminate\Redis\Database'), 'default']);
        $queue->expects($this->once())->method('getRandomId')->will($this->returnValue('foo'));
        $queue->expects($this->once())->method('getSeconds')->with($date)->will($this->returnValue(1));
        $queue->expects($this->once())->method('getTime')->will($this->returnValue(1));

        $redis->shouldReceive('connection')->once()->andReturn($redis);
        $redis->shouldReceive('zadd')->once()->with(
            'queues:default:delayed',
            2,
            json_encode(['job' => 'foo', 'data' => ['data'], 'id' => 'foo', 'attempts' => 1])
        );

        $queue->later($date, 'foo', ['data']);
    }
}
