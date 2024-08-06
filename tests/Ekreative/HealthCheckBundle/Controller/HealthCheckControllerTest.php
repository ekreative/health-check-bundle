<?php

namespace Ekreative\HealthCheckBundle\Controller;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
#[CoversNothing]
class HealthCheckControllerTest extends WebTestCase
{
    public function testAction()
    {
        if (isset($_ENV['travis'])) {
            // This env connects to real redis and mysql servers
            $client = static::createClient(['environment' => 'test_travis']);
        } else {
            // This env uses a sqlite connection
            $client = static::createClient();
        }

        $client->request('GET', '/healthcheck');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('application/json', $client->getResponse()->headers->get('content-type'));

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertCount(2, $data);

        $this->assertIsBool($data['app']);
        $this->assertTrue($data['app']);

        $this->assertIsBool($data['database']);
        $this->assertTrue($data['database']);
    }

    #[Group('redis')]
    public function testActionWithRedis()
    {
        if (isset($_ENV['travis'])) {
            // This env connects to real redis and mysql servers
            $client = static::createClient(['environment' => 'test_travis']);
        } else {
            // This env uses a sqlite connection and fakes the redis server
            $client = static::createClient(['environment' => 'test_with_redis']);

            $redis = $this->getMockBuilder(\Redis::class)
                ->onlyMethods(['ping'])
                ->getMock();
            $redis->method('ping')->willReturn(true);

            $client->getKernel()->getContainer()->set('redis', $redis);
        }

        $client->request('GET', '/healthcheck');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('application/json', $client->getResponse()->headers->get('content-type'));

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertCount(3, $data);

        $this->assertIsBool($data['app']);
        $this->assertTrue($data['app']);

        $this->assertIsBool($data['database']);
        $this->assertTrue($data['database']);

        $this->assertIsBool($data['redis']);
        $this->assertTrue($data['redis']);
    }

    public function testMySQLFailAction()
    {
        $client = static::createClient(['environment' => 'test_with_mysql']);

        $client->request('GET', '/healthcheck');

        $this->assertEquals(503, $client->getResponse()->getStatusCode());
        $this->assertEquals('application/json', $client->getResponse()->headers->get('content-type'));

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertCount(2, $data);

        $this->assertIsBool($data['database']);
        $this->assertFalse($data['database']);
    }

    public function testOptionalMySQLFailAction()
    {
        $client = static::createClient(['environment' => 'test_with_mysql_optional']);

        $client->request('GET', '/healthcheck');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('application/json', $client->getResponse()->headers->get('content-type'));

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertCount(2, $data);

        $this->assertIsBool($data['database']);
        $this->assertFalse($data['database']);
    }

    #[Group('redis')]
    public function testRedisFailAction()
    {
        $client = static::createClient(['environment' => 'test_with_redis']);

        $client->request('GET', '/healthcheck');

        $this->assertEquals(503, $client->getResponse()->getStatusCode());
        $this->assertEquals('application/json', $client->getResponse()->headers->get('content-type'));

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertCount(3, $data);

        $this->assertIsBool($data['redis']);
        $this->assertFalse($data['redis']);
    }

    #[Group('redis')]
    public function testOptionalRedisFailAction()
    {
        $client = static::createClient(['environment' => 'test_with_redis_optional']);

        $client->request('GET', '/healthcheck');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('application/json', $client->getResponse()->headers->get('content-type'));

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertCount(3, $data);

        $this->assertIsBool($data['redis']);
        $this->assertFalse($data['redis']);
    }

    public function testLazyAction()
    {
        $client = static::createClient(['environment' => 'test_lazy']);
        $client->request('GET', '/');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public static function getLazyRedis()
    {
        throw new \Exception('Should not be called');
    }

    #[Group('not-5.4', 'redis')]
    public function testAnnoRoutes()
    {
        // This env uses a sqlite connection and fakes the redis server
        $client = static::createClient(['environment' => 'test_anno']);

        $redis = $this->getMockBuilder(\Redis::class)
            ->onlyMethods(['ping'])
            ->getMock();
        $redis->method('ping')->willReturn(true);

        $client->getKernel()->getContainer()->set('redis', $redis);

        $client->request('GET', '/healthcheck');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('application/json', $client->getResponse()->headers->get('content-type'));
    }
}
