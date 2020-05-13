<?php

namespace Ekreative\HealthCheckBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HealthCheckControllerTest extends WebTestCase
{
    public function testAction()
    {
        if (isset($_ENV['travis'])) {
            // This env connects to real redis and mysql servers
            $client = static::createClient(['environment' => 'test_travis']);
        } else {
            // This env uses a sqlite connection and fakes the redis server
            $client = static::createClient();

            $redis = $this->getMockBuilder(\Redis::class)
                ->setMethods(['ping'])
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
        $this->assertTrue($data['app']);

        $this->assertIsBool($data['redis']);
        $this->assertTrue($data['app']);
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

    public function testAnnoRoutes()
    {
        // This env uses a sqlite connection and fakes the redis server
        $client = static::createClient(['environment' => 'test_anno']);

        $redis = $this->getMockBuilder(\Redis::class)
            ->setMethods(['ping'])
            ->getMock();
        $redis->method('ping')->willReturn(true);

        $client->getKernel()->getContainer()->set('redis', $redis);

        $client->request('GET', '/healthcheck');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('application/json', $client->getResponse()->headers->get('content-type'));
    }
}
