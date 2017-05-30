<?php

namespace Ekreative\HealthCheckBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HealthCheckControllerTest extends WebTestCase
{
    public function testAction()
    {
        $client = static::createClient();

        $redis = $this->getMockBuilder(\Redis::class)->setMethods(['ping'])->getMock();
        $redis->method('ping')->willReturn(true);

        $client->getKernel()->getContainer()->set('redis', $redis);

        $client->request('GET', '/healthcheck');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('application/json', $client->getResponse()->headers->get('content-type'));

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertInternalType('array', $data);
        $this->assertCount(3, $data);

        $this->assertInternalType('bool', $data['app']);
        $this->assertTrue($data['app']);

        $this->assertInternalType('bool', $data['database']);
        $this->assertTrue($data['app']);

        $this->assertInternalType('bool', $data['redis']);
        $this->assertTrue($data['app']);
    }

    public function testFailAction()
    {
        $client = static::createClient();

        $redis = $this->getMockBuilder(\Redis::class)->setMethods(['ping'])->getMock();
        $redis->method('ping')->willThrowException(new \RedisException());
        $client->getKernel()->getContainer()->set('redis', $redis);

        $connection = $this->getMockBuilder(\Doctrine\DBAL\Connection::class)->disableOriginalConstructor()->getMock();
        $connection->method('ping')->willReturn(false);

        $doctrine = $this->getMockBuilder(\Doctrine\Bundle\DoctrineBundle\Registry::class)->disableOriginalConstructor()->getMock();
        $doctrine->method('getConnection')->willReturn($connection);
        $client->getKernel()->getContainer()->set('doctrine', $doctrine);

        $client->request('GET', '/healthcheck');

        $this->assertEquals(503, $client->getResponse()->getStatusCode());
        $this->assertEquals('application/json', $client->getResponse()->headers->get('content-type'));

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertInternalType('array', $data);
        $this->assertCount(3, $data);

        $this->assertInternalType('bool', $data['app']);
        $this->assertTrue($data['app']);

        $this->assertInternalType('bool', $data['database']);
        $this->assertFalse($data['database']);

        $this->assertInternalType('bool', $data['redis']);
        $this->assertFalse($data['redis']);
    }
}
