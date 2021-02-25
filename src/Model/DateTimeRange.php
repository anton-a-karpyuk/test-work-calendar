<?php


namespace App\Model;


use JMS\Serializer\Annotation as Serializer;

class DateTimeRange
{
    /**
     * @Serializer\Type("DateTime<'Y-m-d H:i'>")
     * @var \DateTime
     */
    protected $start;

    /**
     * @Serializer\Type("DateTime<'Y-m-d H:i'>")
     * @var \DateTime
     */
    protected $end;

    /**
     * DateTimeRange constructor.
     */
    public function __construct($start, $end)
    {
        $this->start = gettype($start) == "object"?$start: (new \DateTime($start));
        $this->end = gettype($end) == "object"?$end: (new \DateTime($end));
        $this->postDeserialize();
    }

    /**
     * @Serializer\PostDeserialize()
     */
    public function postDeserialize() {
        $this->setStart($this->start);
        $this->setEnd($this->end);
    }

    /**
     * @return \DateTime
     */
    public function getStart(): \DateTime
    {
        return $this->start;
    }

    /**
     * @param \DateTime $start
     * @return DateTimeRange
     */
    public function setStart(\DateTime $start): DateTimeRange
    {
        $this->start = \DateTime::createFromFormat('Y-m-d H:i:s', $start->format('Y-m-d H:i:00'));
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd(): \DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     * @return DateTimeRange
     */
    public function setEnd(\DateTime $end): DateTimeRange
    {
        $this->end = \DateTime::createFromFormat('Y-m-d H:i:s', $end->format('Y-m-d H:i:00'));
        return $this;
    }

    public static function compare(\DateTime $a, \DateTime $b) : int {
        return intval($a->getTimestamp()) - intval($b->getTimestamp());
    }

    public function intersects(\DateTime $date) : bool {
        return static::compare($date, $this->start) >= 0 && static::compare($date, $this->end) < 0;
    }

}
