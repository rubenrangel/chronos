<?php
/**
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @copyright     Copyright (c) Brian Nesbitt <brian@nesbot.com>
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Cake\Chronos\Traits;

use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;

/**
 * Provides a number of datetime related factory methods.
 */
trait FactoryTrait
{
    /**
     * Create a ChronosInterface instance from a DateTimeInterface one
     *
     * @param DateTimeInterface $dt The datetime instance to convert.
     * @return static
     */
    public static function instance(DateTimeInterface $dt)
    {
        if ($dt instanceof static) {
            return clone $dt;
        }
        return new static($dt->format('Y-m-d H:i:s.u'), $dt->getTimeZone());
    }

    /**
     * Create a ChronosInterface instance from a string.  This is an alias for the
     * constructor that allows better fluent syntax as it allows you to do
     * ChronosInterface::parse('Monday next week')->fn() rather than
     * (new Chronos('Monday next week'))->fn()
     *
     * @param string|null $time The strtotime compatible string to parse
     * @param DateTimeZone|string|null $tz The DateTimeZone object or timezone name.
     * @return $this
     */
    public static function parse($time = null, $tz = null)
    {
        return new static($time, $tz);
    }

    /**
     * Get a ChronosInterface instance for the current date and time
     *
     * @param DateTimeZone|string|null $tz The DateTimeZone object or timezone name.
     * @return static
     */
    public static function now($tz = null)
    {
        return new static(null, $tz);
    }

    /**
     * Create a ChronosInterface instance for today
     *
     * @param DateTimeZone|string|null $tz The timezone to use.
     * @return static
     */
    public static function today($tz = null)
    {
        return new static('midnight', $tz);
    }

    /**
     * Create a ChronosInterface instance for tomorrow
     *
     * @param DateTimeZone|string|null $tz The DateTimeZone object or timezone name the new instance should use.
     * @return static
     */
    public static function tomorrow($tz = null)
    {
        return new static('tomorrow, midnight', $tz);
    }

    /**
     * Create a ChronosInterface instance for yesterday
     *
     * @param DateTimeZone|string|null $tz The DateTimeZone object or timezone name the new instance should use.
     * @return static
     */
    public static function yesterday($tz = null)
    {
        return new static('yesterday, midnight', $tz);
    }

    /**
     * Create a ChronosInterface instance for the greatest supported date.
     *
     * @return \Cake\Chronos\ChronosInterface
     * @return \Cake\Chronos\ChronosInterface
     */
    public static function maxValue()
    {
        return static::createFromTimestamp(PHP_INT_MAX);
    }

    /**
     * Create a ChronosInterface instance for the lowest supported date.
     *
     * @return \Cake\Chronos\ChronosInterface
     */
    public static function minValue()
    {
        $max = PHP_INT_SIZE === 4 ? PHP_INT_MAX : PHP_INT_MAX / 10;
        return static::createFromTimestamp(~$max);
    }

    /**
     * Create a new ChronosInterface instance from a specific date and time.
     *
     * If any of $year, $month or $day are set to null their now() values
     * will be used.
     *
     * If $hour is null it will be set to its now() value and the default values
     * for $minute and $second will be their now() values.
     * If $hour is not null then the default values for $minute and $second
     * will be 0.
     *
     * @param int|null $year The year to create an instance with.
     * @param int|null $month The month to create an instance with.
     * @param int|null $day The day to create an instance with.
     * @param int|null $hour The hour to create an instance with.
     * @param int|null $minute The minute to create an instance with.
     * @param int|null $second The second to create an instance with.
     * @param DateTimeZone|string|null $tz The DateTimeZone object or timezone name the new instance should use.
     * @return static
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
     * Create a ChronosInterface instance from just a date. The time portion is set to now.
     *
     * @param int $year The year to create an instance with.
     * @param int $month The month to create an instance with.
     * @param int $day The day to create an instance with.
     * @param DateTimeZone|string|null $tz The DateTimeZone object or timezone name the new instance should use.
     * @return static
     */
    public static function createFromDate($year = null, $month = null, $day = null, $tz = null)
    {
        return static::create($year, $month, $day, null, null, null, $tz);
    }

    /**
     * Create a ChronosInterface instance from just a time. The date portion is set to today.
     *
     * @param int|null $hour The hour to create an instance with.
     * @param int|null $minute The minute to create an instance with.
     * @param int|null $second The second to create an instance with.
     * @param DateTimeZone|string|null $tz The DateTimeZone object or timezone name the new instance should use.
     * @return static
     */
    public static function createFromTime($hour = null, $minute = null, $second = null, $tz = null)
    {
        return static::create(null, null, null, $hour, $minute, $second, $tz);
    }

    /**
     * Create a ChronosInterface instance from a specific format
     *
     * @param string $format The date() compatible format string.
     * @param string $time The formatted date string to interpret.
     * @param DateTimeZone|string|null $tz The DateTimeZone object or timezone name the new instance should use.
     * @return static
     * @throws InvalidArgumentException
     */
    public static function createFromFormat($format, $time, $tz = null)
    {
        if ($tz !== null) {
            $dt = parent::createFromFormat($format, $time, static::safeCreateDateTimeZone($tz));
        } else {
            $dt = parent::createFromFormat($format, $time);
        }

        if ($dt instanceof DateTimeInterface) {
            return static::instance($dt);
        }

        $errors = static::getLastErrors();
        throw new InvalidArgumentException(implode(PHP_EOL, $errors['errors']));
    }

    /**
     * Create a ChronosInterface instance from a timestamp
     *
     * @param int $timestamp The timestamp to create an instance from.
     * @param DateTimeZone|string|null $tz The DateTimeZone object or timezone name the new instance should use.
     * @return static
     */
    public static function createFromTimestamp($timestamp, $tz = null)
    {
        return static::now($tz)->setTimestamp($timestamp);
    }

    /**
     * Create a ChronosInterface instance from an UTC timestamp
     *
     * @param int $timestamp The UTC timestamp to create an instance from.
     * @return static
     */
    public static function createFromTimestampUTC($timestamp)
    {
        return new static('@' . $timestamp);
    }

    /**
     * Creates a DateTimeZone from a string or a DateTimeZone
     *
     * @param DateTimeZone|string|null $object The value to convert.
     * @return DateTimeZone
     * @throws InvalidArgumentException
     */
    protected static function safeCreateDateTimeZone($object)
    {
        if ($object === null) {
            return new DateTimeZone(date_default_timezone_get());
        }

        if ($object instanceof DateTimeZone) {
            return $object;
        }

        return new DateTimeZone($object);
    }
}
