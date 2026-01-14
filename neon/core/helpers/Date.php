<?php
namespace neon\core\helpers;

class Date
{
	const UK_DATE_FORMAT = "d/m/Y";
	const UK_DATE_HOURS_MINS_FORMAT = "d/m/Y H:i";
	const UK_DATE_HOURS_MINS_SECS_FORMAT = "d/m/Y H:i:s";
	const MYSQL_DATE_FORMAT = 'Y-m-d';
	const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

	/**
	 * strtotime with support for uk style d/m/y dates rather than the stupid US default m/d/y
	 * Note that the "+0000" ensures that it's treated correctly if subsequently cast to a day
	 * @param string $date
	 *
	 * @return int|false unix timestamp or false if it was not possible to parse the date successfully
	 */
	public static function strtotimeUk(string $date)
	{
		if(preg_match("%^\\d{1,2}/\\d{1,2}/\\d{2,4}(.*)$%", $date)) {
			if(strpos($date, " ")!==false) {
				list($date, $time) = explode(" ", $date);
			}
			list($day, $month, $year) = explode("/", $date);
			$date = $year . "-" . $month . "-" . $day . (isset($time) ? " " . $time : " +0000");
		}
		elseif (strpos($date, " ")===false) {
			$date.=" +0000";
		}
		return strtotime($date);
	}

	/**
	 * ensure that the thing string is a \Datetime if possible
	 *
	 * @param mixed $thing
	 *
	 * @return \DateTime|null
	 */
	public static function dateTimeUk(mixed $thing): ?\DateTime
	{
		if ($thing===null) return null;
		if ($thing instanceof \DateTime) {
			return $thing;
		}
		$ts = self::strtotimeUk($thing);
		if ($ts) {
			$dt = \DateTime::createFromFormat('U', $ts);
			$dt->setTimezone(self::getTimezone());
			return $dt;
		}
		return null;
	}

	/**
	 * helper since if you try to use modify on a DateTime it will alter it in place and so may have unexpected side
	 * effects elsewhere if you use it incorrectly, this method returns a new modified DateTime instead
	 * @param \DateTime $dateTime
	 * @param string $modifyString
	 * @return \DateTime
	 * @throws \Exception
	 */
	public static function modified(\DateTime $dateTime, string $modifyString): \DateTime
	{
		$dt = new \DateTime(null, $dateTime->getTimezone());
		$dt->setTimestamp($dateTime->getTimestamp());
		if (!$dt->modify($modifyString)) {
			throw new \Exception("Bad modify string '".$modifyString."'");
		}
		return $dt;
	}

	/**
	 * @param string $dateString
	 * @param string $format
	 * @param \DateTimeZone|null $timezone
	 * @return bool
	 */
	public static function validate(
		string $dateString,
		string $format,
		\DateTimeZone $timezone=null): bool
	{
		if (!$timezone) {
			$timezone = self::getTimezone();
		}
		return \DateTime::createFromFormat($format, $dateString, $timezone)!==false;
	}

	/**
	 * generate a period of time as a human-readable string from a \DateTime object
	 *
	 * @param \DateTimeInterface $datetime
	 * @param int $significance
	 * @param string $separator
	 * @param \DateTimeInterface|null $targetDate get the date diff between 2 date objects otherwise uses now
	 *
	 * @return string
	 */
	public static function readableDateDiff(
		\DateTimeInterface $datetime,
		int $significance = 1,
		string $separator = ', ',
		\DateTimeInterface $targetDate = null): string
	{
		if(!$targetDate instanceof \DateTimeInterface) {
			$targetDate = new \DateTime();
		}
		$interval = $targetDate->diff($datetime);

		$doPlural = function ($nb, $str) { return $nb > 1 ? $str . 's' : $str; }; // adds plurals

		$format = [];
		if($interval->y !== 0) {
			$format[] = "%y " . $doPlural($interval->y, "year");
		}
		if($interval->m !== 0) {
			$format[] = "%m " . $doPlural($interval->m, "month");
		}
		if($interval->d !== 0) {
			$format[] = "%d " . $doPlural($interval->d, "day");
		}
		if($interval->h !== 0) {
			$format[] = "%h " . $doPlural($interval->h, "hour");
		}
		if($interval->i !== 0) {
			$format[] = "%i " . $doPlural($interval->i, "minute");
		}

		if(!count($format)) {
			return "less than a minute";
		} else {
			$format[] = "%s " . $doPlural($interval->s, "second");
		}

		$actual_significance = count($format);
		if($actual_significance < $significance){
			$significance = $actual_significance;
		}

		$format_parts = [];
		for($i = 1; $i <= $significance; $i++) {
			$format_parts[] = array_shift($format);
		}

		return $interval->format(implode($separator, $format_parts));
	}

	/**
	 * if there is a db setting for main.devDateNow that will be used (this allows you to spoof the current time!)
	 * @param null|string $modifyString optional modifier
	 * @return \DateTime
	 * @throws \Exception
	 */
	public static function nowDateTime(string $modifyString=null): \DateTime
	{
		$spoofedDateTime = self::getDevSpoofedDateTime();
		if ($spoofedDateTime instanceof \DateTime) {
			$nowDt = $spoofedDateTime;
		}
		else {
			$nowDt = new \DateTime('now', self::getTimezone());
		}
		if ($modifyString) {
			$nowDt->modify($modifyString);
		}
		return $nowDt;
	}

	/**
	 * checks if there is spoofed date in the db setting main.devDateNow
	 * @return null|\DateTime
	 */
	public static function getDevSpoofedDateTime(): ?\DateTime
	{
		if (env('NEON_ENV')==='dev' && ($devDateNow = neon()->settingsManager->get('main', 'devDateNow'))) {
			$dt = \DateTime::createFromFormat(self::MYSQL_DATE_FORMAT, $devDateNow, self::getTimezone());
			if ($dt!==false) return $dt;
		}
		return null;
	}

	/**
	 * @return \DateTimeZone
	 */
	public static function getTimezone(): \DateTimeZone
	{
		static $timezone = null;
		if ($timezone===null) {
			$timezone = new \DateTimeZone(env('NEON_TIMEZONE'));
		}
		return $timezone;
	}

	/**
	 * get an array of daily dates between two dates (inclusive)
	 * @param string $fromDate must be a parsable date string
	 * @param string $toDate must be a parsable date string after fromDate
	 * @param string $format defaults to mysql date format Y-m-d
	 * @throws \Exception
	 */
	public static function getDateRange(string $fromDate, string $toDate, string $format=self::MYSQL_DATE_FORMAT): array
	{
		$fromDateTs = self::dateTimeUk($fromDate);
		if (!$fromDateTs) {
			throw new \Exception("Failed to parse date '".$fromDate."'");
		}
		$toDateTs = self::dateTimeUk($toDate)->setTime(23,59,59);//ensure it's the end of the day
		if (!$toDateTs) {
			throw new \Exception("Failed to parse date '".$toDate."'");
		}
		if ($fromDateTs>$toDateTs) {
			throw new \Exception("'".$fromDate."' comes after '".$toDate."'");
		}
		$period = new \DatePeriod(
			$fromDateTs,
			new \DateInterval('P1D'),
			$toDateTs,
		);
		$range = [];
		foreach ($period as $dateTs) {
			$range[]=$dateTs->format($format);
		}
		return $range;
	}

}


