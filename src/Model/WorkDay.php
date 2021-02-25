<?php


namespace App\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

class WorkDay
{
    /**
     * @var \DateTime
     * @Serializer\Type("DateTime<'Y-m-d'>")
     */
    protected $date;
    /**
     * @var DateTimeRange
     * @Serializer\Exclude()
     */
    protected $dateTimePeriod;

    /**
     * @var Collection
     */
    protected $timeRanges;

    /**
     * DateTimeRange constructor.
     */
    public function __construct(\DateTime $date, Collection $timeRanges)
    {
        $this->date = (clone $date)->setTime(0,0,0);
        $this->dateTimePeriod = new DateTimeRange(clone $this->date, (clone $this->date)->setTime(23,59,00));
        $this->timeRanges = clone $timeRanges;
    }

    public function hasTimeRanges() {
        return $this->timeRanges->count() > 0;
    }

    public function excludeDateTimeRange(DateTimeRange $period) {
        //Не пересекает вообще
        if ($period->getStart() > $this->dateTimePeriod->getEnd() || $period->getEnd() < $this->dateTimePeriod->getStart())
            return;

        //Перекрывает весь день
        if ($period->getStart() < $this->dateTimePeriod->getStart() && $period->getEnd() > $this->dateTimePeriod->getEnd()) {
            $this->timeRanges->clear();
            return;
        }

        //Ищем итоговые периоды
        $timePeriod = new TimeRange($this->dateTimePeriod->getStart(), $this->dateTimePeriod->getEnd());
        if ($period->getStart() > $this->dateTimePeriod->getStart()) {
            $timePeriod->setStart($period->getStart());
        }

        if ($period->getEnd() < $this->dateTimePeriod->getEnd()) {
            $timePeriod->setEnd($period->getEnd());
        }

        $newRanges = [];
        /** @var TimeRange $timeRange */
        foreach ($this->timeRanges as $timeRange) {
            array_push($newRanges, ...$timeRange->exclude($timePeriod));
        }

        $this->timeRanges = new ArrayCollection($newRanges);
    }
}
