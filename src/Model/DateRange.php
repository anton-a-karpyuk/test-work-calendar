<?php


namespace App\Model;

use JMS\Serializer\Annotation as Serializer;

class DateRange extends DateTimeRange
{
    /**
     * @Serializer\Type("DateTime<'m-d'>")
     * @var \DateTime
     */
    protected $start;

    /**
     * @Serializer\Type("DateTime<'m-d'>")
     * @var \DateTime
     */
    protected $end;

    /**
     * DateRange constructor.
     * @param \DateTime|string $start
     * @param \DateTime|string $end
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
     * @param \DateTime $start
     * @return DateTimeRange
     */
    public function setStart(\DateTime $start): DateTimeRange
    {
        $this->start = (clone $start)->setTime(0,0,0);
        return $this;
    }

    /**
     * @param \DateTime $end
     * @return DateTimeRange
     */
    public function setEnd(\DateTime $end): DateTimeRange
    {
        $this->end = (clone $end)->setTime(23,59,00);
        return $this;
    }

    public static function compare(\DateTime $a, \DateTime $b) : int {
        $dM = intval($a->format('m')) - intval($b->format('m'));
        $dD = intval($a->format('d')) - intval($b->format('d'));
        return $dM == 0 ? $dD : $dM;
    }
}
