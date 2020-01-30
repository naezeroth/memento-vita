<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Recurr\Recurrence;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\ArrayTransformerConfig;

/**
 * @ORM\Entity(repositoryClass="App\Repository\HabitRepository")
 */
class Habit
{
    private static $frequencyList = array(0 => 'DAILY', 1 => 'WEEKLY', 2 => 'MONTHLY', 3 => 'YEARLY');
    private static $weekDays = array(0 => 'SU', 1 => 'MO', 2 => 'TU', 3 => 'WE', 4 => 'TH', 5 => 'FR', 6 => 'SA');

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Goal", inversedBy="habits")
     * @ORM\JoinColumn(nullable=false)
     */
    private $goal;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="object", nullable=true)
     */
    private $done;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="habits")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userBelongsTo;

    /**
     * @var \DateTime - indicates 'first date' of recurring timeframe
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateStart;

    /**
     * @var \DateTime - indicates 'until date' of rRule
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateUntil;

    /**
     * @var int - indicates 'how often' rRule applies
     * @ORM\Column(type="integer", nullable=true)
     */
    private $count;

    /**
     * @var int - indicates 'how often' per frequency, ex. 1 means every month, 2 every other month
     * @ORM\Column(type="integer", nullable=true, name="freq_interval")
     */
    private $interval;

    /**
     * @var int - indicates 'frequency', uses self::$frequencyList
     * @ORM\Column(type="integer", nullable=true)
     */
    private $freq;

    /**
     * @var array - indicates 'every day', uses self::$weekDays
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $byDay;

    /**
     * @var array - integer indicates 'day of month', e.g. 1 is first day of month, -1 is last day
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $byMonthDay;

    /**
     * @var array - integer indicates 'day of year', e.g. 1 is first day of year, -1 is last day of year
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $byYearDay;

    /**
     * @var array - indicates 'every month' January is 0, December = 12
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $byMonth;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGoal(): ?Goal
    {
        return $this->goal;
    }

    public function setGoal(?Goal $goal): self
    {
        $this->goal = $goal;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDone()
    {
        if($this->done == null){
            $this->setDone(array_fill(0, self::getCount(), false));
        }
        return $this->done;
    }

    public function setDone($done): self
    {
        $this->done = $done;

        return $this;
    }

    public function getUserBelongsTo(): ?User
    {
        return $this->userBelongsTo;
    }

    public function setUserBelongsTo(?User $userBelongsTo): self
    {
        $this->userBelongsTo = $userBelongsTo;

        return $this;
    }

    /**
     * @return array
     */
    public static function getWeekDays(): array
    {
        return self::$weekDays;
    }

    /**
     * @param array $weekDays
     */
    public static function setWeekDays(array $weekDays): void
    {
        self::$weekDays = $weekDays;
    }

    /**
     * @return \DateTime
     */
    public function getDateStart() //\DateTime
    {
        return $this->dateStart;
    }

    /**
     * @param \DateTime $dateStart
     */
    public function setDateStart(\DateTime $dateStart) //: void
    {
        $this->dateStart = $dateStart;
    }

    /**
     * @return \DateTime
     */
    public function getDateUntil() //: \DateTime
    {
        return $this->dateUntil;
    }

    /**
     * @param \DateTime $dateUntil
     */
    public function setDateUntil(\DateTime $dateUntil) //: void
    {
        $this->dateUntil = $dateUntil;
    }

    /**
     * @return int
     */
    public function getCount() //: int
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount(int $count) //: void
    {
        $this->count = $count;
    }

    /**
     * @return int
     */
    public function getInterval() //: int
    {
        return $this->interval;
    }

    /**
     * @param int $interval
     */
    public function setInterval(int $interval) //: void
    {
        $this->interval = $interval;
    }

    /**
     * @return int
     */
    public function getFreq() //: int
    {
        return $this->freq;
    }

    /**
     * @param int $freq
     */
    public function setFreq(int $freq) //: void
    {
        $this->freq = $freq;
    }

    /**
     * @return array
     */
    public function getByDay() //: array
    {
        return $this->byDay;
    }

    /**
     * @param array $byDay
     */
    public function setByDay(array $byDay)//: void
    {
        $this->byDay = $byDay;
    }

    /**
     * @return array
     */
    public function getByMonthDay()//: array
    {
        return $this->byMonthDay;
    }

    /**
     * @param array $byMonthDay
     */
    public function setByMonthDay(array $byMonthDay)//: void
    {
        $this->byMonthDay = $byMonthDay;
    }

    /**
     * @return array
     */
    public function getByYearDay()//: array
    {
        return $this->byYearDay;
    }

    /**
     * @param array $byYearDay
     */
    public function setByYearDay(array $byYearDay)//: void
    {
        $this->byYearDay = $byYearDay;
    }

    /**
     * @return array
     */
    public function getByMonth()//: array
    {
        return $this->byMonth;
    }

    /**
     * @param array $byMonth
     */
    public function setByMonth(array $byMonth) //: void
    {
        $this->byMonth = $byMonth;
    }


    /**
     * @return array
     */
    public static function getFrequencyList()//: array
    {
        return self::$frequencyList;
    }

    /**
     * @param array $frequencyList
     */
    public static function setFrequencyList(array $frequencyList)//: void
    {
        self::$frequencyList = $frequencyList;
    }

    /**
     * @return null|\DateTime[]
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     */
    public function getRecurrenceCollection()
    {

        if ($this->getFreq() === null) {
            return null;
        }

        $result = array();

        if ($this->getFreq() !== null) {
            $rulePart[] = 'FREQ=' . self::$frequencyList[$this->getFreq()];
        }

        if ($this->getCount() !== null) {
            $rulePart[] = 'COUNT=' . $this->getCount();
        }

//        if ($this->getDateUntil() !== null) {
//            $rulePart[] = 'UNTIL=' . TimeUtil::canonicalDateFormat($this->getDateUntil());
//        }

        if ($this->getInterval() === null) {
            $rulePart[] = 'INTERVAL=1';
        } else {
            $rulePart[] = 'INTERVAL=' . $this->getInterval();
        }
//        if (count($this->getByDay()) > 0) {
//            $byWeekDayArray = array();
//            foreach ($this->getByDay() as $byDay) {
//                $byWeekDayArray[] = self::$weekDays[$byDay];
//            }
//
//            $rulePart[] = 'BYDAY=' . implode(',', $byWeekDayArray);
//        }
//        if (count($this->getByMonth()) > 0) {
//            $rulePart[] = 'BYMONTH=' . implode(',', $this->getByMonth());
//        }
//        if (count($this->getByMonthDay()) > 0) {
//            $rulePart[] = 'BYMONTHDAY=' . implode(',', $this->getByMonthDay());
//        }
//        if (count($this->getByYearDay()) > 0) {
//            $rulePart[] = 'BYYEARDAY=' . implode(',', $this->getByYearDay());
//        }

        $ruleString = implode(';', $rulePart);

        $rule = new Rule($ruleString,  $this->getDateStart(), $this->getDateUntil(), \DateTimeZone::AUSTRALIA );

//        $rule = new Rule();
//
//
//        $rule->setStartDate($this->getDateStart());
//        $rule->setEndDate($this->getDateUntil());
//        $rule->setFreq(self::$frequencyList[$this->getFreq()]);
//        $rule->setInterval($this->getInterval());

        $transformer = new ArrayTransformer();

        // enable fix for MONTHLY
        if ($this->getFreq() === 2) {
            $transformerConfig = new ArrayTransformerConfig();
            $transformerConfig->enableLastDayOfMonthFix();
            $transformer->setConfig($transformerConfig);
        }

        $elements = $transformer->transform($rule);

        /** @var Recurrence $element */
        foreach ($elements as $element) {
            $result[] = $element->getEnd();
        }

        return $result;
    }

}
