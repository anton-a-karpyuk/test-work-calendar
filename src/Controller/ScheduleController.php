<?php

namespace App\Controller;

use App\Model\DateRange;
use App\Model\DateTimeRange;
use App\Model\WorkDay;
use App\Model\Worker;
use Google_Client;
use Google_Service_Calendar;
use http\Exception\InvalidArgumentException;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ScheduleController extends AbstractController
{
    private $serializer;
    private $google_client;
    /**
     * ScheduleController constructor.
     */
    public function __construct(SerializerInterface $serializer, Google_Client $google_client)
    {
        $this->serializer = $serializer;

        $google_client->setApplicationName('Google Calendar API Russian Holidays');
        $google_client->setScopes(Google_Service_Calendar::CALENDAR_READONLY);
        $google_client->setAccessType('offline');

        $this->google_client = $google_client;

    }

    private function getHolidays(?\DateTime $start_date, ?\DateTime $end_date) : array {
        $client = $this->google_client;

        $service = new Google_Service_Calendar($client);

        $calendarId = 'en.russian#holiday@group.v.calendar.google.com';
        $optParams = array(
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => $start_date->format('c'),
            'timeMax' => $end_date->format('c'),
        );
        $results = $service->events->listEvents($calendarId, $optParams);
        return array_map(function(\Google_Service_Calendar_Event $item) {
            return new DateTimeRange($item->getStart()->getDate(), $item->getEnd()->getDate());
        },  $results->getItems());
    }

    private function getSchedule(int $worker_id, \DateTime $start_date, \DateTime $end_date) : array {
        /**
         * Имитация получения данных >>>
         */
        $party_raw = json_encode($this->getParameter('party'));
        /** @var DateTimeRange $party */
        $party = $this->serializer->deserialize($party_raw, DateTimeRange::class, 'json');
        $workers_raw = json_encode($this->getParameter('workers'));
        $workers = $this->serializer->deserialize($workers_raw, "ArrayCollection<App\Model\Worker>", 'json');
        /**
         * <<< Имитация получения данных
         */

        $is_found = false;
        /** @var Worker $worker */
        $worker = null;
        foreach($workers as $worker) {
            if ($worker->getId() == $worker_id) {
                $is_found = true;
                break;
            }
        }

        if (!$is_found)
            throw new InvalidArgumentException("Работник не найден");

        $holidays = $this->getHolidays($start_date, $end_date);

        $period = new \DatePeriod(
            $start_date,
            new \DateInterval('P1D'),
            $end_date
        );

        $days = [];

        /** @var DateRange $lastHoliday */
        $lastHoliday = current($holidays);

        /**
         * @var integer $key
         * @var \DateTime $value
         */
        foreach ($period as $key => $value) {
            //Check for holidays
            if ($lastHoliday && $lastHoliday->intersects($value)) {
                $lastHoliday = next($holidays);
                continue;
            }

            //Check for weekend?
            //if ($value->format('N') > 5)
            //    continue;

            //Check for worker vacations
            $is_found = false;
            /** @var DateRange $vacation */
            foreach ($worker->getVacations() as $vacation ) {
                if ($vacation->intersects($value)) {
                    $is_found = true;
                    break;
                }
            }
            if ($is_found)
                continue;

            //Check for party
            $day = new WorkDay($value, $worker->getWorkingHours());
            $day->excludeDateTimeRange($party);

            if ($day->hasTimeRanges()) {
                $days[] = $day;
            }
        }
        return $days;
    }

    /**
     * @Route("/schedule", name="schedule")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $worker_id = $request->query->get('userId');
        $start_date = new \DateTime($request->query->get('startDate'));
        $end_date = new \DateTime($request->query->get('endDate'));

        return new Response($this->serializer->serialize($this->getSchedule($worker_id, $start_date, $end_date), 'json'), Response::HTTP_OK, ['Content-Type' => 'application/json;charset=utf8']);
    }
}
