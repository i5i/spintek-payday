<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostControllerTest extends WebTestCase
{
    public function testPaydayRequest(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/2024');
        $this->assertResponseIsSuccessful();
        $response = $client->getResponse();
        $content = $response->getContent();
        $jsonData = json_decode(json_decode($content,true),true);
        $this->assertArrayHasKey('paydayDate', $jsonData[0]);
        $this->assertArrayHasKey('notificationDate', $jsonData[0]);
        $this->assertArrayHasKey('paydayDate', $jsonData[11]);
        $this->assertArrayHasKey('notificationDate', $jsonData[11]);
    }
}
