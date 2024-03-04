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
        if ($dayOfMonth <= 0){
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

            //adjust payday date if necessary
            $paydayDate = $this->adjustPayday($paydayDate);
            $notificationDate = $this->getNotificationDate($paydayDate, $notifyAheadByDays);

            $returnArray[] = [
                'paydayDate' => $paydayDate->format($this->outputFormat),
                'notificationDate' => $notificationDate->format($this->outputFormat),
            ];
        }

        return $returnArray;
    }

    private function moveDateToPrevFriday(DatePoint $date)
    {
        if ($date->format('N') == 7) {
            //if sunday 
            $date = $date->modify('-2 day');
        } elseif ($date->format('N') == 6) {
            //if saturday 
            $date = $date->modify('-1 day');
        }
        return $date;
    }

    private function getNotificationDate(DatePoint $notificationDate, int $notifyAheadByDays)
    {
        //count work days while skipping past public holidays and weekends 
        $util = $this->holidayUtil;
        for ($i = 1; $i <= $notifyAheadByDays; $i++) {
            $notificationDate = $notificationDate->modify('-1 day');
            while (
                $util->isHoliday('EE', $notificationDate->format('Y-m-d')) ||
                $notificationDate->format('N') >= 6
            ) {
                $notificationDate = $notificationDate->modify('-1 day');
            }
        }

        return $notificationDate;
    }

    private function adjustPayday(DatePoint $paydayDate)
    {
        //check day does not fall on weekend
        $paydayDate = $this->moveDateToPrevFriday($paydayDate);

        //check against public holidays
        $util = $this->holidayUtil;

        while ($util->isHoliday('EE', $paydayDate->format('Y-m-d'))) {
            $paydayDate = $paydayDate->modify('-1 day');
        }

        return $paydayDate;
    }
}
