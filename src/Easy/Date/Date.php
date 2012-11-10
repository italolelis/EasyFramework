<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.easyframework.net>.
 */

namespace Easy\Date;

use DateInterval;
use DateTime;
use DateTimeZone;
use Easy\Generics\IClonable;
use Easy\Generics\IEquatable;
use InvalidArgumentException;

/**
 * Represents an instant in time, typically expressed as a date and time of day.
 */
class Date extends DateTime implements IEquatable, IClonable
{

    const SUNDAY = 0;
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;
    const MONTHS_PER_YEAR = 12;
    const HOURS_PER_DAY = 24;
    const MINUTES_PER_HOUR = 60;
    const SECONDS_PER_MINUTE = 60;

    protected static function safeCreateDateTimeZone($object)
    {
        if ($object instanceof DateTimeZone) {
            return $object;
        }

        $tz = @timezone_open((string) $object);

        if ($tz === false) {
            throw new InvalidArgumentException('Unknown or bad timezone (' . $object . ')');
        }

        return $tz;
    }

    /**
     * Create a new instance of Date class
     * @param string $time
     * @param DateTimeZone $tz
     */
    public function __construct($time = null, $tz = null)
    {
        if ($tz !== null) {
            parent::__construct($time, static::safeCreateDateTimeZone($tz));
        } else {
            parent::__construct($time);
        }
    }

    /**
     * Create a new instance of Date class, bringing the default format and timezone
     * @param DateTime $dt
     * @return Date
     */
    public static function instance(DateTime $dt)
    {
        return new Date($dt->format('Y-m-d H:i:s'), $dt->getTimeZone());
    }

    /**
     * Create a new instance of Date class, based on current time
     * @param DateTimeZone $tz
     * @return Date
     */
    public static function now($tz = null)
    {
        return new Date(null, $tz);
    }

    /**
     * Create a new instance of Date class, based on current day
     * @param DateTimeZone $tz
     * @return Date
     */
    public static function today($tz = null)
    {
        return Date::now($tz)->startOfDay();
    }

    /**
     * Create a new instance of Date class, based on tomorrow's day
     * @param DateTimeZone $tz
     * @return Date
     */
    public static function tomorrow($tz = null)
    {
        return Date::now($tz)->startOfDay()->addDay();
    }

    /**
     * Create a new instance of Date class, based on yesterday's day
     * @param DateTimeZone $tz
     * @return Date
     */
    public static function yesterday($tz = null)
    {
        return Date::now($tz)->startOfDay()->subDay();
    }

    /**
     * Create a date based on parameters
     * @param int $year The year
     * @param int $month The month as integer value
     * @param int $day The day as integer value
     * @param int $hour The hour
     * @param int $minute The minute
     * @param int $second The secound
     * @param DateTimeZone $tz The DateTimeZone object
     * @return Date
     */
    public static function create($year = null, $month = null, $day = null, $hour = null, $minute = null, $second = null, $tz = null)
    {
        $year = ($year === null) ? date('Y') : $year;
        $month = ($month === null) ? date('n') : $month;
        $day = ($day === null) ? date('j') : $day;

        if ($hour === null) {
            $hour = date('G');
            $minute = ($minute === null) ? date('i') : $minute;
            $second = ($second === null) ? date('s') : $second;
        } else {
            $minute = ($minute === null) ? 0 : $minute;
            $second = ($second === null) ? 0 : $second;
        }

        return static::createFromFormat('Y-n-j G:i:s', sprintf('%s-%s-%s %s:%02s:%02s', $year, $month, $day, $hour, $minute, $second), $tz);
    }

    /**
     * Create a date based on date parameters
     * @param int $year The year
     * @param int $month The month as integer value
     * @param int $day The day as integer value
     * @param DateTimeZone $tz The DateTimeZone object
     * @return Date
     */
    public static function createFromDate($year = null, $month = null, $day = null, $tz = null)
    {
        return static::create($year, $month, $day, null, null, null, $tz);
    }

    /**
     * Create a date based on time parameters
     * @param int $hour The hour
     * @param int $minute The minute
     * @param int $second The secound
     * @param DateTimeZone $tz The DateTimeZone object
     * @return Date
     */
    public static function createFromTime($hour = null, $minute = null, $second = null, $tz = null)
    {
        return static::create(null, null, null, $hour, $minute, $second, $tz);
    }

    /**
     * Create a Date Object from a desired format
     * @param string $format
     * @param string $time
     * @param DateTimeZone $object
     * @return Date
     * @throws InvalidArgumentException
     */
    public static function createFromFormat($format, $time, $object = null)
    {
        if ($object !== null) {
            $dt = parent::createFromFormat($format, $time, static::safeCreateDateTimeZone($object));
        } else {
            $dt = parent::createFromFormat($format, $time);
        }

        if ($dt instanceof DateTime) {
            return static::instance($dt);
        }

        $errors = DateTime::getLastErrors();
        throw new InvalidArgumentException(implode(PHP_EOL, $errors['errors']));
    }

    /**
     * Create a date based on timestap
     * @param string $timestamp
     * @param DateTimeZone $tz The DateTimeZone object
     * @return Date
     */
    public static function createFromTimestamp($timestamp, $tz = null)
    {
        return static::now($tz)->setTimestamp($timestamp);
    }

    /**
     * Create a date based on UTC timestamp
     * @param int $timestamp
     * @return Date
     */
    public static function createFromTimestampUTC($timestamp)
    {
        return new Date('@' . $timestamp);
    }

    /**
     * Create a date based on current locale
     * @param string $date The date to be parsed
     * @return Date
     */
    public static function createFromLocale($date)
    {
        return new Date(str_replace("/", "-", $date));
    }

    /**
     * {@inheritdoc}
     */
    public function copy()
    {
        return static::instance($this);
    }

    public function __get($name)
    {
        if ($name == 'year')
            return intval($this->format('Y'));
        if ($name == 'month')
            return intval($this->format('n'));
        if ($name == 'day')
            return intval($this->format('j'));
        if ($name == 'hour')
            return intval($this->format('G'));
        if ($name == 'minute')
            return intval($this->format('i'));
        if ($name == 'second')
            return intval($this->format('s'));
        if ($name == 'dayOfWeek')
            return intval($this->format('w'));
        if ($name == 'dayOfYear')
            return intval($this->format('z'));
        if ($name == 'weekOfYear')
            return intval($this->format('W'));
        if ($name == 'daysInMonth')
            return intval($this->format('t'));
        if ($name == 'timestamp')
            return intval($this->format('U'));
        if ($name == 'age')
            return intval($this->diffInYears());
        if ($name == 'quarter')
            return intval(($this->month - 1) / 3) + 1;
        if ($name == 'offset')
            return $this->getOffset();
        if ($name == 'offsetHours')
            return $this->getOffset() / static::SECONDS_PER_MINUTE / static::MINUTES_PER_HOUR;
        if ($name == 'dst')
            return $this->format('I') == '1';
        if ($name == 'timezone')
            return $this->getTimezone();
        if ($name == 'timezoneName')
            return $this->getTimezone()->getName();
        if ($name == 'tz')
            return $this->timezone;
        if ($name == 'tzName')
            return $this->timezoneName;
        throw new InvalidArgumentException(sprintf("Unknown getter '%s'", $name));
    }

    public function __set($name, $value)
    {
        $handled = true;

        switch ($name) {
            case 'year':
                parent::setDate($value, $this->month, $this->day);
                break;
            case 'month':
                parent::setDate($this->year, $value, $this->day);
                break;
            case 'day':
                parent::setDate($this->year, $this->month, $value);
                break;
            case 'hour':
                parent::setTime($value, $this->minute, $this->second);
                break;
            case 'minute':
                parent::setTime($this->hour, $value, $this->second);
                break;
            case 'second':
                parent::setTime($this->hour, $this->minute, $value);
                break;
            case 'timestamp':
                parent::setTimestamp($value);
                break;
            case 'timezone':
                $this->setTimezone($value);
                break;
            case 'tz':
                $this->setTimezone($value);
                break;
            default:
                $handled = false;
                break;
        }

        if (!$handled) {
            throw new InvalidArgumentException(sprintf("Unknown getter '%s'", $name));
        }
    }

    public function year($value)
    {
        $this->year = $value;

        return $this;
    }

    public function month($value)
    {
        $this->month = $value;

        return $this;
    }

    public function day($value)
    {
        $this->day = $value;

        return $this;
    }

    /**
     * Set a date
     * @param int $year
     * @param int $month
     * @param int $day
     * @return Date
     */
    public function setDate($year, $month, $day)
    {
        return $this->year($year)->month($month)->day($day);
    }

    public function hour($value)
    {
        $this->hour = $value;

        return $this;
    }

    public function minute($value)
    {
        $this->minute = $value;

        return $this;
    }

    public function second($value)
    {
        $this->second = $value;

        return $this;
    }

    /**
     * Set the time
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @return Date
     */
    public function setTime($hour, $minute, $second = 0)
    {
        return $this->hour($hour)->minute($minute)->second($second);
    }

    /**
     * Sets the DateTime values
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @return Date
     */
    public function setDateTime($year, $month, $day, $hour, $minute, $second)
    {
        return $this->setDate($year, $month, $day)->setTime($hour, $minute, $second);
    }

    public function timestamp($value)
    {
        $this->timestamp = $value;

        return $this;
    }

    /**
     * Sets timezone
     * @param string $value The DataTimeZone string
     * @return Date
     */
    public function setTimezone($value)
    {
        parent::setTimezone(static::safeCreateDateTimeZone($value));
        return $this;
    }

    /**
     * Get the date string by locale
     * @param string $format The strftime format
     * @return string
     */
    public function toI18nFormat($format = "%x")
    {
        return strftime($format, $this->getTimestamp());
    }

    public function __toString()
    {
        return $this->toDateTimeString();
    }

    /**
     * Get the Date String "Y-m-d" format
     * @return string
     */
    public function toDateString()
    {
        return $this->format('Y-m-d');
    }

    /**
     * Get the date string "M J, Y" format
     * @return string
     */
    public function toFormattedDateString()
    {
        return $this->format('M j, Y');
    }

    /**
     * Get the time string "H:i:s" format
     * @return string
     */
    public function toTimeString()
    {
        return $this->format('H:i:s');
    }

    /**
     * Get the date string "Y-m-d H:i:s" format
     * @return string
     */
    public function toDateTimeString()
    {
        return $this->format('Y-m-d H:i:s');
    }

    /**
     * Get the date string "D, M j, Y g:i A" format
     * @return string
     */
    public function toDayDateTimeString()
    {
        return $this->format('D, M j, Y g:i A');
    }

    /**
     * Get the date ATOM format
     * @return string
     */
    public function toATOMString()
    {
        return $this->format(DateTime::ATOM);
    }

    /**
     * Get the date COOKIE format
     * @return string
     */
    public function toCOOKIEString()
    {
        return $this->format(DateTime::COOKIE);
    }

    /**
     * Get the date ISO8601 format
     * @return string
     */
    public function toISO8601String()
    {
        return $this->format(DateTime::ISO8601);
    }

    /**
     * Get the date RFC822 format
     * @return string
     */
    public function toRFC822String()
    {
        return $this->format(DateTime::RFC822);
    }

    /**
     * Get the date RFC850 format
     * @return string
     */
    public function toRFC850String()
    {
        return $this->format(DateTime::RFC850);
    }

    /**
     * Get the date RFC1036 format
     * @return string
     */
    public function toRFC1036String()
    {
        return $this->format(DateTime::RFC1036);
    }

    /**
     * Get the date RFC1123 format
     * @return string
     */
    public function toRFC1123String()
    {
        return $this->format(DateTime::RFC1123);
    }

    /**
     * Get the date RFC2822 format
     * @return string
     */
    public function toRFC2822String()
    {
        return $this->format(DateTime::RFC2822);
    }

    /**
     * Get the date RFC3339 format
     * @return string
     */
    public function toRFC3339String()
    {
        return $this->format(DateTime::RFC3339);
    }

    /**
     * Get the date RSS format
     * @return string
     */
    public function toRSSString()
    {
        return $this->format(DateTime::RSS);
    }

    /**
     * Get the date W3C format
     * @return string
     */
    public function toW3CString()
    {
        return $this->format(DateTime::W3C);
    }

    /**
     * {@inheritdoc}
     */
    public function equals($date)
    {
        return $this === $date;
    }

    /**
     * Verifies if a date is greater than other date
     * @param Date $dt
     * @return boolean
     */
    public function greaterThan(Date $dt)
    {
        return $this > $dt;
    }

    /**
     * Verifies if a date is greater or equals than other date
     * @param Date $dt
     * @return boolean
     */
    public function greaterOrEqualsThan(Date $dt)
    {
        return $this >= $dt;
    }

    /**
     * Verifies if a date is less than other date
     * @param Date $dt
     * @return boolean
     */
    public function lessThan(Date $dt)
    {
        return $this < $dt;
    }

    /**
     * Verifies if a date is less or equals than other date
     * @param Date $dt
     * @return boolean
     */
    public function lessOrEqualsThan(Date $dt)
    {
        return $this <= $dt;
    }

    /**
     * Verifies if is weekday
     * @return boolean
     */
    public function isWeekday()
    {
        return ($this->dayOfWeek != static::SUNDAY && $this->dayOfWeek != static::SATURDAY);
    }

    /**
     * Verifies if is weekend
     * @return boolean
     */
    public function isWeekend()
    {
        return !$this->isWeekDay();
    }

    /**
     * Verifies if is yesterday
     * @return boolean
     */
    public function isYesterday()
    {
        return $this->toDateString() === static::now($this->tz)->subDay()->toDateString();
    }

    /**
     * Verifies if is today
     * @return boolean
     */
    public function isToday()
    {
        return $this->toDateString() === static::now($this->tz)->toDateString();
    }

    /**
     * Verifies if is tomorrow
     * @return boolean
     */
    public function isTomorrow()
    {
        return $this->toDateString() === static::now($this->tz)->addDay()->toDateString();
    }

    /**
     * Verifies if is future
     * @return boolean
     */
    public function isFuture()
    {
        return $this->gt(static::now($this->tz));
    }

    /**
     * Verifies if is past
     * @return boolean
     */
    public function isPast()
    {
        return !$this->isFuture();
    }

    /**
     * Verifies if is lap year
     * @return boolean
     */
    public function isLeapYear()
    {
        return $this->format('L') == '1';
    }

    /**
     * Add years to current date
     * @param int $value
     * @return Date
     */
    public function addYears($value)
    {
        $interval = new DateInterval(sprintf("P%dY", abs($value)));
        if ($value >= 0) {
            $this->add($interval);
        } else {
            $this->sub($interval);
        }

        return $this;
    }

    /**
     * Add one year to current date
     * @return Date
     */
    public function addYear()
    {
        return $this->addYears(1);
    }

    /**
     * Subtract one year on current date
     * @return Date
     */
    public function subYear()
    {
        return $this->addYears(-1);
    }

    /**
     * Subtract years on current date
     * @param int $value
     * @return Date
     */
    public function subYears($value)
    {
        return $this->addYears(-1 * $value);
    }

    /**
     * Add months to current date
     * @param int $value
     * @return Date
     */
    public function addMonths($value)
    {
        $interval = new DateInterval(sprintf("P%dM", abs($value)));
        if ($value >= 0) {
            $this->add($interval);
        } else {
            $this->sub($interval);
        }

        return $this;
    }

    /**
     * Add one month to current date
     * @return Date
     */
    public function addMonth()
    {
        return $this->addMonths(1);
    }

    /**
     * Subtract one month on current date
     * @return Date
     */
    public function subMonth()
    {
        return $this->addMonths(-1);
    }

    /**
     * Subtract months on current date
     * @param int $value
     * @return Date
     */
    public function subMonths($value)
    {
        return $this->addMonths(-1 * $value);
    }

    /**
     * Add days to current date
     * @param int $value
     * @return Date
     */
    public function addDays($value)
    {
        $interval = new DateInterval(sprintf("P%dD", abs($value)));
        if ($value >= 0) {
            $this->add($interval);
        } else {
            $this->sub($interval);
        }

        return $this;
    }

    /**
     * Add one day to current date
     * @return Date
     */
    public function addDay()
    {
        return $this->addDays(1);
    }

    /**
     * Subtract one day on current date
     * @return Date
     */
    public function subDay()
    {
        return $this->addDays(-1);
    }

    /**
     * Subtract days on current date
     * @return Date
     */
    public function subDays($value)
    {
        return $this->addDays(-1 * $value);
    }

    /**
     * Add weekdays to current date
     * @param int $value
     * @return Date
     */
    public function addWeekdays($value)
    {
        $absValue = abs($value);
        $direction = $value < 0 ? -1 : 1;

        while ($absValue > 0) {
            $this->addDays($direction);

            while ($this->isWeekend()) {
                $this->addDays($direction);
            }

            $absValue--;
        }

        return $this;
    }

    /**
     * Add one weekday to current date
     * @return Date
     */
    public function addWeekday()
    {
        return $this->addWeekdays(1);
    }

    /**
     * Subtract one weekday on current date
     * @return Date
     */
    public function subWeekday()
    {
        return $this->addWeekdays(-1);
    }

    /**
     * Subtract weekdays on current date
     * @return Date
     */
    public function subWeekdays($value)
    {
        return $this->addWeekdays(-1 * $value);
    }

    /**
     * Add weeks to current date
     * @param int $value
     * @return Date
     */
    public function addWeeks($value)
    {
        $interval = new DateInterval(sprintf("P%dW", abs($value)));
        if ($value >= 0) {
            $this->add($interval);
        } else {
            $this->sub($interval);
        }

        return $this;
    }

    /**
     * Add one week to current date
     * @return Date
     */
    public function addWeek()
    {
        return $this->addWeeks(1);
    }

    /**
     * Subtract one week on current date
     * @return Date
     */
    public function subWeek()
    {
        return $this->addWeeks(-1);
    }

    /**
     * Subtract weeks on current date
     * @param int $value
     * @return Date
     */
    public function subWeeks($value)
    {
        return $this->addWeeks(-1 * $value);
    }

    /**
     * Add hours to current time
     * @param int $value
     * @return Date
     */
    public function addHours($value)
    {
        $interval = new DateInterval(sprintf("PT%dH", abs($value)));
        if ($value >= 0) {
            $this->add($interval);
        } else {
            $this->sub($interval);
        }

        return $this;
    }

    /**
     * Add one hour to current time
     * @return Date
     */
    public function addHour()
    {
        return $this->addHours(1);
    }

    /**
     * Sibtract hour on current time
     * @return Date
     */
    public function subHour()
    {
        return $this->addHours(-1);
    }

    /**
     * Sibtract hours on current time
     * @param int $value
     * @return Date
     */
    public function subHours($value)
    {
        return $this->addHours(-1 * $value);
    }

    /**
     * Add minutes to current time
     * @param int $value
     * @return Date
     */
    public function addMinutes($value)
    {
        $interval = new DateInterval(sprintf("PT%dM", abs($value)));
        if ($value >= 0) {
            $this->add($interval);
        } else {
            $this->sub($interval);
        }

        return $this;
    }

    /**
     * Add one minute to current time
     * @return Date
     */
    public function addMinute()
    {
        return $this->addMinutes(1);
    }

    /**
     * Subtract one minute to current time
     * @return Date
     */
    public function subMinute()
    {
        return $this->addMinutes(-1);
    }

    /**
     * Subtract minutes on current time
     * @param int $value
     * @return Date
     */
    public function subMinutes($value)
    {
        return $this->addMinutes(-1 * $value);
    }

    /**
     * Add minutes on current time
     * @param int $value
     * @return Date
     */
    public function addSeconds($value)
    {
        $interval = new DateInterval(sprintf("PT%dS", abs($value)));
        if ($value >= 0) {
            $this->add($interval);
        } else {
            $this->sub($interval);
        }

        return $this;
    }

    /**
     * Add one minute on current time
     * @return Date
     */
    public function addSecond()
    {
        return $this->addSeconds(1);
    }

    /**
     * Subtract one secound on current time
     * @return Date
     */
    public function subSecond()
    {
        return $this->addSeconds(-1);
    }

    /**
     * Subtract secounds on current time
     * @param int $value
     * @return Date
     */
    public function subSeconds($value)
    {
        return $this->addSeconds(-1 * $value);
    }

    /**
     * Set the current date to the start of the day
     * @return Date
     */
    public function startOfDay()
    {
        return $this->hour(0)->minute(0)->second(0);
    }

    /**
     * Set the current date to the end of the day
     * @return Date
     */
    public function endOfDay()
    {
        return $this->hour(23)->minute(59)->second(59);
    }

    /**
     * Set the current date to the start of the month
     * @return Date
     */
    public function startOfMonth()
    {
        return $this->startOfDay()->day(1);
    }

    /**
     * Set the current date to the end of the month
     * @return Date
     */
    public function endOfMonth()
    {
        return $this->day($this->daysInMonth)->endOfDay();
    }

    public function diffInYears(Date $dt = null, $abs = true)
    {
        $dt = ($dt === null) ? Date::now($this->tz) : $dt;
        $sign = ($abs) ? '' : '%r';

        return intval($this->diff($dt)->format($sign . '%y'));
    }

    public function diffInMonths(Date $dt = null, $abs = true)
    {
        $dt = ($dt === null) ? Date::now($this->tz) : $dt;
        list($sign, $years, $months) = explode(':', $this->diff($dt)->format('%r:%y:%m'));
        $value = ($years * static::MONTHS_PER_YEAR) + $months;

        if ($sign === '-' && !$abs) {
            $value = $value * -1;
        }

        return $value;
    }

    public function diffInDays(Date $dt = null, $abs = true)
    {
        $dt = ($dt === null) ? Date::now($this->tz) : $dt;
        $sign = ($abs) ? '' : '%r';

        return intval($this->diff($dt)->format($sign . '%a'));
    }

    public function diffInHours(Date $dt = null, $abs = true)
    {
        $dt = ($dt === null) ? Date::now($this->tz) : $dt;

        return intval($this->diffInMinutes($dt, $abs) / static::MINUTES_PER_HOUR);
    }

    public function diffInMinutes(Date $dt = null, $abs = true)
    {
        $dt = ($dt === null) ? Date::now($this->tz) : $dt;

        return intval($this->diffInSeconds($dt, $abs) / static::SECONDS_PER_MINUTE);
    }

    public function diffInSeconds(Date $dt = null, $abs = true)
    {
        $dt = ($dt === null) ? Date::now($this->tz) : $dt;
        list($sign, $days, $hours, $minutes, $seconds) = explode(':', $this->diff($dt)->format('%r:%a:%h:%i:%s'));
        $value = ($days * static::HOURS_PER_DAY * static::MINUTES_PER_HOUR * static::SECONDS_PER_MINUTE) +
                ($hours * static::MINUTES_PER_HOUR * static::SECONDS_PER_MINUTE) +
                ($minutes * static::SECONDS_PER_MINUTE) +
                $seconds;

        if ($sign === '-' && !$abs) {
            $value = $value * -1;
        }

        return intval($value);
    }

    /**
     * When comparing a value in the past to default now:
     * 1 hour ago
     * 5 months ago
     *
     * When comparing a value in the future to default now:
     * 1 hour from now
     * 5 months from now
     *
     * When comparing a value in the past to another value:
     * 1 hour before
     * 5 months before
     *
     * When comparing a value in the future to another value:
     * 1 hour after
     * 5 months after
     */
    public function diffForHumans(Date $other = null)
    {
        $txt = '';

        $isNow = $other === null;

        if ($isNow) {
            $other = static::now();
        }

        $isFuture = $this->gt($other);

        $delta = abs($other->diffInSeconds($this));

        // 30 days per month, 365 days per year... good enough!!
        $divs = array(
            'second' => static::SECONDS_PER_MINUTE,
            'minute' => static::MINUTES_PER_HOUR,
            'hour' => static::HOURS_PER_DAY,
            'day' => 30,
            'month' => 12
        );

        $unit = 'year';

        foreach ($divs as $divUnit => $divValue) {
            if ($delta < $divValue) {
                $unit = $divUnit;
                break;
            }

            $delta = floor($delta / $divValue);
        }

        if ($delta == 0) {
            $delta = 1;
        }

        $txt = $delta . ' ' . $unit;
        $txt .= $delta == 1 ? '' : 's';

        if ($isNow) {
            if ($isFuture) {
                return $txt . ' from now';
            }

            return $txt . ' ago';
        }

        if ($isFuture) {
            return $txt . ' after';
        }

        return $txt . ' before';
    }

    /**
     * Check if the variable is a date or not
     * @param string $str_date
     * @return bool
     */
    public static function isDate($date)
    {
        if ($date) {
            if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Check if the variable is a date or not
     * @param string $str_date
     * @return bool
     */
    public static function isDateTime($date)
    {
        if ($date) {
            if (preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/", $date)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Returns a partial SQL string to search for all records between two dates.
     *
     * @param integer|string|DateTime $begin UNIX timestamp, strtotime() valid string or DateTime object
     * @param integer|string|DateTime $end UNIX timestamp, strtotime() valid string or DateTime object
     * @param string $fieldName Name of database field to compare with
     * @param string|DateTimeZone $timezone Timezone string or DateTimeZone object
     * @return string Partial SQL string.
     */
    public static function daysAsSql($begin, $end, $fieldName, $timezone = null)
    {
        $dateBeging = Date::createFromFormat("d-m-Y", $begin);
        $dateEnd = Date::createFromFormat("d-m-Y", $end);
        $begin = $dateBeging . ' 00:00:00';
        $end = $dateEnd . ' 23:59:59';

        return "($fieldName >= '$begin') AND ($fieldName <= '$end')";
    }

    /**
     * Returns a partial SQL string to search for all records between two times
     * occurring on the same day.
     *
     * @param integer|string|DateTime $dateString UNIX timestamp, strtotime() valid string or DateTime object
     * @param string $fieldName Name of database field to compare with
     * @param string|DateTimeZone $timezone Timezone string or DateTimeZone object
     * @return string Partial SQL string.
     */
    public static function dayAsSql($dateString, $fieldName, $timezone = null)
    {
        return static::daysAsSql($dateString, $dateString, $fieldName);
    }

}