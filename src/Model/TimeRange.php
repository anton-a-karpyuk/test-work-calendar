<?php


namespace App\Model;

use JMS\Serializer\Annotation as Serializer;

class TimeRange extends DateTimeRange
{
    /**
     * @Serializer\Type("DateTime<'H:i'>")
     * @var \DateTime
     */
    protected $start;

    /**
     * @Serializer\Type("DateTime<'H:i'>")
     * @var \DateTime
     */
    protected $end;

    /**
     * TimeRange constructor.
     * @param \DateTime|string $start
     * @param \DateTime|string $end
     */
    public function __construct($start, $end)
    {
        $this->start = gettype($start) == "object"?$start: (new \DateTime($start));
        $this->end = gettype($end) == "object"?$end: (new \DateTime($end));
    }

    /**
     * @Serializer\PostDeserialize()
     */
    public function postDeserialize() {
        $this->setStart($this->start);
        $this->setEnd($this->end);
    }

    /**
     * @param \DateTime $start
     * @return DateTimeRange
     */
    public function setStart(\DateTime $start): DateTimeRange
    {
        $this->start = \DateTime::createFromFormat('Y-m-d H:i:s', $start->format('1970-01-01 H:i:00'));
        return $this;
    }

    /**
     * @param \DateTime $end
     * @return DateTimeRange
     */
    public function setEnd(\DateTime $end): DateTimeRange
    {
        $this->end = \DateTime::createFromFormat('Y-m-d H:i:s', $end->format('1970-01-01 H:i:00'));
        return $this;
    }

    public static function compare(\DateTime $a, \DateTime $b) : int {
        $dH = intval($a->format('H')) - intval($b->format('H'));
        $dI = intval($a->format('i')) - intval($b->format('i'));
        return $dH == 0 ? $dI : $dH;
    }


    public function exclude(TimeRange $time) : array {
        // = $this - time
        if (static::compare($time->getStart(), $this->getEnd()) > 0 || static::compare($time->getEnd(), $this->getStart()) < 0)
            return [$this];

        if (static::compare($time->getStart(), $this->getStart()) < 0 && static::compare($time->getEnd(), $this->getEnd()) > 0) {
            return [];
        }

        $ranges = [];
        if (static::compare($time->getStart(), $this->getStart()) > 0)
            $ranges[] = new TimeRange($this->getStart(), $time->getStart());
        if (static::compare($time->getEnd(), $this->getEnd()) < 0)
            $ranges[] = new TimeRange($time->getEnd(), $this->getEnd());
        return $ranges;
    }
}
