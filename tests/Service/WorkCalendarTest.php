<?php

namespace App\Tests\Service;

use App\Service\WorkCalendar;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use InvalidArgumentException;

class WorkCalendarTest extends KernelTestCase
{
    public function testPaydayScheduel(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $workCalendar = $container->get(WorkCalendar::class);
        $paydayScheduel = $workCalendar->getPaydaySchedule(2024, 33);

        $this->assertEquals([
            [
                "paydayDate" => "2024-01-31",
                "notificationDate" => "2024-01-26"
            ],
            [
                "paydayDate" => "2024-02-29",
                "notificationDate" => "2024-02-26"
            ],
            [
                "paydayDate" => "2024-03-28",
                "notificationDate" => "2024-03-25"
            ],
            [
                "paydayDate" => "2024-04-30",
                "notificationDate" => "2024-04-25"
            ],
            [
                "paydayDate" => "2024-05-31",
                "notificationDate" => "2024-05-28"
            ],
            [
                "paydayDate" => "2024-06-28",
                "notificationDate" => "2024-06-25"
            ],
            [
                "paydayDate" => "2024-07-31",
                "notificationDate" => "2024-07-26"
            ],
            [
                "paydayDate" => "2024-08-30",
                "notificationDate" => "2024-08-27"
            ],
            [
                "paydayDate" => "2024-09-30",
                "notificationDate" => "2024-09-25"
            ],
            [
                "paydayDate" => "2024-10-31",
                "notificationDate" => "2024-10-28"
            ],
            [
                "paydayDate" => "2024-11-29",
                "notificationDate" => "2024-11-26"
            ],
            [
                "paydayDate" => "2024-12-31",
                "notificationDate" => "2024-12-23"
            ]
        ], $paydayScheduel);

        $this->expectException(InvalidArgumentException::class);
        $workCalendar->getPaydaySchedule(2024, 0);
    }
}
