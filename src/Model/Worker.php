<?php


namespace App\Model;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

class Worker
{
    /**
     * @Serializer\Type("integer")
     * @var integer
     */
    protected $id;

    /**
     * @Serializer\Type("ArrayCollection<App\Model\TimeRange>")
     * @var Collection
     */
    protected $working_hours;

    /**
     * @Serializer\Type("ArrayCollection<App\Model\DateRange>")
     * @var Collection
     */
    protected $vacations;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }


    /**
     * @return Collection
     */
    public function getWorkingHours(): Collection
    {
        return $this->working_hours;
    }

    /**
     * @param Collection $working_hours
     * @return Worker
     */
    public function setWorkingHours(Collection $working_hours): Worker
    {
        $this->working_hours = $working_hours;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getVacations(): Collection
    {
        return $this->vacations;
    }

    /**
     * @param Collection $vacations
     * @return Worker
     */
    public function setVacations(Collection $vacations): Worker
    {
        $this->vacations = $vacations;
        return $this;
    }


}
