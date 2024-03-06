<?php

namespace App\Service;

use Symfony\Component\Clock\DatePoint;
use Checkdomain\Holiday\Util as HolidayUtil;
use InvalidArgumentException;

class WorkCalendar
{
    private string $outputFormat = 'Y-m-d';
    private HolidayUtil $holidayUtil;

    /**
     * setOutputFormat
     *
     * Setter for outputFormat.
     * WorkCalendar will return dates in this format.
     * Default value is 'Y-m-d'.
     *
     * @param  string $outputFormat
     * @return void
     */
    public function setOutputFormat(string $outputFormat): void
    {
        $this->outputFormat = $outputFormat;
    }

    /**
     * setHolidayUtil
     *
     * Setter for HolidayUtil.
     *
     * @param  HolidayUtil $holidayUtil
     * @return void
     */
    public function setHolidayUtil(HolidayUtil $holidayUtil): void
    {
        $this->holidayUtil = $holidayUtil;
    }

    /**
     * getPaydaySchedule
     *
     * Returns array of 12 arrays each containing a notification and payday date as strings.
     *
     * @param  int $year Return values for this year. Required.
     * @param  int $dayOfMonth Payday of each month. Required.
     * @param  int $notifyAheadByDays Notification date set in advanced by this many work days . Defaults to 3. Optional.
     * @return array Example of output:
     *     [
     *         0...11 =>
     *         [
     *             notificationDate => '2024-12-07',
     *             paydayDate => '2024-12-10',
     *         ]
     *     ]
     */
    public function getPaydaySchedule(int $year, int $dayOfMonth, int $notifyAheadByDays = 3): array
    {
        if ($dayOfMonth <= 0) {
            throw new InvalidArgumentException("Day of month must be configured as a positive integer.");
        }

        $returnArray = [];

        for ($month = 1; $month <= 12; $month++) {
            //if day of month is set to later than the current month e.g. feburary 30
            $day = $dayOfMonth;
            while (!checkdate($month, $day, $year) && $day > 28) {
                $day--;
            }

            $paydayDate = new DatePoint("$year-$month-$day");
            $this->setHolidayUtil(new HolidayUtil());

            //move date back in time if it falls on a non-workday
            $paydayDate = $this->adjustDate($paydayDate);
            $notificationDate = $this->getNotificationDate($paydayDate, $notifyAheadByDays);

            $returnArray[] = [
                'paydayDate' => $paydayDate->format($this->outputFormat),
                'notificationDate' => $notificationDate->format($this->outputFormat),
            ];
        }

        return $returnArray;
    }

    private function getNotificationDate(DatePoint $notificationDate, int $notifyAheadByDays)
    {
        //count work days while skipping past public holidays and weekends
        $util = $this->holidayUtil;
        for ($i = 1; $i <= $notifyAheadByDays; $i++) {
            $notificationDate = $notificationDate->modify('-1 day');
            $notificationDate = $this->adjustDate($notificationDate);
        }

        return $notificationDate;
    }

    private function adjustDate(DatePoint $date)
    {
        $util = $this->holidayUtil;
        //check against public holidays and weekends
        while (
            $util->isHoliday('EE', $date->format('Y-m-d')) ||
            $date->format('N') >= 6
        ) {
            $date = $date->modify('-1 day');
        }

        return $date;
    }
}
