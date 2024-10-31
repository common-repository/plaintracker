<?php
namespace Plainware;

class Time
{
	const SECONDS_IN_DAY = 24*60*60;

	public static $t;

	public $timeFormat;
	public $dateFormat;
	public $weekStartsOn;
	public $timezone = '';

	public static $months = [ 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ];
	public static $weekdays = [ 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ];

	public function __construct( $timeFormat = 'g:ia', $dateFormat = 'j M Y', $weekStartsOn = 7 )
	{
		// _print_r( func_get_args() );
		$this->timeFormat = $timeFormat;
		$this->dateFormat = $dateFormat;
		$this->weekStartsOn = $weekStartsOn;
	}

	public function getDateFormat()
	{
		return $this->dateFormat;
	}

	public static function t()
	{
		if( null === static::$t ) static::$t = new \DateTime;
		return static::$t;
	}

	protected static function _convert( \DateTime $t )
	{
		$date = $t->format( 'Ymd' );

		$h = $t->format('G');
		$m = $t->format('i');

		$h = str_pad( $h, 2, 0, STR_PAD_LEFT );
		$m = str_pad( $m, 2, 0, STR_PAD_LEFT );

		$ret = $date . $h . $m;

		return $ret;
	}

	public static function getYearMonth( $dateTimeDb )
	{
		$ret = (int) substr( $dateTimeDb, 0, 6 );
		return $ret;
	}

	public static function getYear( $dateTimeDb )
	{
		$ret = (int) substr( $dateTimeDb, 0, 4 );
		return $ret;
	}

	public static function getMonth( $dateTimeDb )
	{
		$ret = (int) substr( $dateTimeDb, 4, 2 );
		return $ret;
	}

	public static function getDay( $dateTimeDb )
	{
		$ret = (int) substr( $dateTimeDb, 6, 2 );
		return $ret;
	}

	public static function getHour( $dateTimeDb )
	{
		$ret = (int) substr( $dateTimeDb, 8, 2 );
		return $ret;
	}

	public static function getMinute( $dateTimeDb )
	{
		$ret = (int) substr( $dateTimeDb, 10, 2 );
		return $ret;
	}

	public static function getDate( $dateTimeDb )
	{
		static $ret = [];
		if( ! isset($ret[$dateTimeDb]) ){
			$ret[$dateTimeDb] = substr( $dateTimeDb, 0, 8 );
		}
		return $ret[$dateTimeDb];
	}

	public static function getTime( $dateTimeDb )
	{
		// $ret = static::getHour( $dateTimeDb ) . static::getMinute( $dateTimeDb );
		$ret = substr( $dateTimeDb, 8, 4 );
		return $ret;
	}

	public static function getTimeInSeconds( $dateTimeDb )
	{
		$h = (int) static::getHour( $dateTimeDb );
		$m = (int) static::getMinute( $dateTimeDb );
		$ret = 60 * 60 * $h + 60 * $m;
		return $ret;
	}

	public static function fromYearMonthDay( $y, $m, $d )
	{
		$y = str_pad( $y, 4, 0, STR_PAD_LEFT );
		$m = str_pad( $m, 2, 0, STR_PAD_LEFT );
		$d = str_pad( $d, 2, 0, STR_PAD_LEFT );
		$ret = $y . $m . $d;
		return $ret;
	}

	public static function toUtc( $dateTimeDb )
	{
		$ret = $dateTimeDb;
		return $ret;
	}

	public static function convertTimezone( $dateTimeDb, $fromTzString, $toTzString )
	{
		$t = static::t();

		$fromTz = new DateTimeZone( $fromTzString );
		$t->setTimezone( $fromTz );

		$t->setDate( static::getYear($dateTimeDb), static::getMonth($dateTimeDb), static::getDay($dateTimeDb) );
		$t->setTime( static::getHour($dateTimeDb), static::getMinute($dateTimeDb) );

		$toTz = new DateTimeZone( $toTzString );
		$t->setTimezone( $toTz );

		$ret = static::_convert( $t );

		return $ret;
	}

	public static function fromDateSeconds( $dateDb, $timeInSeconds )
	{
		// if( strlen($dateDb) > 8 ){
			// $dateDb = substr( $dateDb, 0, 8 );
		// }

		while( $timeInSeconds > self::SECONDS_IN_DAY ){
			$dateDb = static::getNextDate( $dateDb );
			$timeInSeconds = $timeInSeconds - self::SECONDS_IN_DAY;
		}

		$timeDb = static::formatTimeInDayDb( $timeInSeconds );
		$ret = $dateDb . $timeDb;

		return $ret;
	}

	public static function getWeekday( $dateDb )
	{
		static $cache = [];
		if( isset($cache[$dateDb]) ){
			return $cache[$dateDb];
		}

		$t = static::t();
		$t->setDate( static::getYear($dateDb), static::getMonth($dateDb), static::getDay($dateDb) );
		$ret = $t->format('w');
	// sunday?
		if( 0 == $ret ) $ret = 7;
		$cache[ $dateDb ] = $ret;

		return $ret;
	}

	public function getWeekdays()
	{
		$ret = [];

		$wkds = [ 1, 2, 3, 4, 5, 6, 7 ];

	// sort
		$sooner = $later = [];
		sort( $wkds );
		reset( $wkds );
		foreach( $wkds as $wd ){
			if( $wd < $this->weekStartsOn )
				$later[] = $wd;
			else
				$sooner[] = $wd;
		}
		$wkds = array_merge( $sooner, $later );

		reset( $wkds );
		foreach( $wkds as $wkd ){
			$ret[ $wkd ] = $wkd;
		}

		return $ret;
	}

	public function getFormatMonths()
	{
		return static::$months;
	}

	public function getFormatWeekdays()
	{
		$wkdList = $this->getWeekdays();

		$ret = [];
		foreach( $wkdList as $wkd ){
			$ret[ $wkd ] = $this->formatWeekday( $wkd );
		}

		return $ret;
	}

	public function formatWeekdays( array $wkds )
	{
		$all = $this->getWeekdays();

		$ret = [];
		foreach( $all as $wkd ){
			if( ! in_array($wkd, $wkds) ) continue;
			$ret[] = $this->formatWeekday( $wkd );
		}

		$ret = join( ', ', $ret );
		return $ret;
	}

	public function formatWeekday( $wkd )
	{
		if( strlen($wkd) > 7 ){
			$wkd = $this->getWeekday( $wkd );
		}

		$wkd = (int) $wkd;
		$ret = '__' . static::$weekdays[ $wkd - 1 ] . '__';

		return $ret;
	}

	public function getDates( $startDate, $endDate )
	{
		$ret = [];

		$rex = $startDate;
		while( $rex <= $endDate ){
			$ret[ $rex ] = [ static::getStartDay($rex), static::getEndDay($rex) ];
			$rex = static::getNextDate( $rex );
		}

		return $ret;
	}

	public static function getNow()
	{
		$t = static::t()->setTimestamp( time() );
		$ret = static::_convert( $t );
		return $ret;
	}

	public function getStartWeek( $dateTimeDb )
	{
		$wkd = $this->getWeekday( $dateTimeDb );
		$wkds = $this->getWeekdays();

		$myPosInWeek = 0;
		foreach( $wkds as $test ){
			if( $wkd == $test ) break;
			$myPosInWeek++;
		}

		$dateDb = $dateTimeDb;
		if( strlen($dateDb) > 8 ){
			$dateDb = substr( $dateDb, 0, 8 );
		}

		$ret = static::fromDateSeconds( $dateDb, 0 );
		if( $myPosInWeek ){
			$ret = Time::modify( $ret, '- ' . $myPosInWeek . ' days' );
		}

		return $ret;
	}

	public function getEndWeek( $dateTimeDb )
	{
		$wkd = $this->getWeekday( $dateTimeDb );
		$wkds = $this->getWeekdays();

		$myPosInWeek = 0;
		foreach( array_reverse($wkds) as $test ){
			if( $wkd == $test ) break;
			$myPosInWeek++;
		}

		$ret = static::fromDateSeconds( $dateTimeDb, 0 );
		if( $myPosInWeek ){
			$ret = Time::modify( $ret, '+ ' . $myPosInWeek . ' days' );
		}
		$ret = static::getEndDay( $ret );

		return $ret;
	}

	public static function getStartYear( $dateTimeDb )
	{
		$ret = substr( $dateTimeDb, 0, 4 ) . '0101';
		$ret = static::getStartDay( $ret );
		return $ret;
	}

	public static function getEndYear( $dateTimeDb )
	{
		$ret = substr( $dateTimeDb, 0, 4 ) . '1231';
		$ret = static::getEndDay( $ret );
		return $ret;
	}

	public static function getStartMonth( $dateTimeDb )
	{
		$ret = substr( $dateTimeDb, 0, 6 ) . '010000';
		return $ret;
	}

	public static function isStartDay( $dateTimeDb )
	{
		return ( '0000' == substr($dateTimeDb, -4) );
	}

	public static function isEndDay( $dateTimeDb )
	{
		return ( '2400' == substr($dateTimeDb, -4) );
	}

	public static function getEndMonth( $dateTimeDb )
	{
		$daysInMonth = [ 1 => 31, 3 => 31, 4 => 30, 5 => 31, 6 => 30, 7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31 ];
		$mo = (int) static::getMonth( $dateTimeDb );

		if( isset($daysInMonth[$mo]) ){
			$ret = substr( $dateTimeDb, 0, 6 ) . $daysInMonth[$mo];
			$ret = static::getEndDay( $ret );
		}
		else {
			$ret = $dateTimeDb;
			$ret = static::modify( $ret, '+30 days' );
			$ret = static::getStartMonth( $ret );
			$ret = static::modify( $ret, '-1 day' );
			$ret = static::getEndDay( $ret );
		}

		return $ret;
	}

	public static function getStartDay( $dateTimeDb )
	{
		$ret = substr( $dateTimeDb, 0, 8 ) . '0000';
		return $ret;
	}

	public static function getEndDay( $dateTimeDb )
	{
		$ret = substr( $dateTimeDb, 0, 8 ) . '2400';
		return $ret;
	}

	public function getWeekNo( $dateDb )
	{
		$t = static::t();

		$t->setDate( static::getYear($dateDb), static::getMonth($dateDb), static::getDay($dateDb) );
		$ret = $t->format( 'W' ); 

	// but it works out of the box for week starts on monday
		$weekday = static::getWeekday( $dateDb );
		if( ! $weekday ){ // sunday
			if( ! $this->weekStartsOn ){
				$ret = $ret + 1;
			}
		}

		return $ret;
	}

	public static function getNextDate( $date )
	{
		static $cache = [];

		$key = $date;
		if( isset($cache[$key]) ){
			return $cache[$key];
		}

		$ret = static::getDate( static::modify($date, '+1 day') );
		$cache[$key] = $ret;

		return $cache[$key];
	}

	public static function getPrevDate( $date )
	{
		static $cache = [];

		$key = $date;
		if( isset($cache[$key]) ){
			return $cache[$key];
		}

		$ret = static::getDate( static::modify($date, '-1 day') );
		$cache[$key] = $ret;

		return $cache[$key];
	}

	public static function formatMonthName( $monthNo )
	{
		if( $monthNo > 12 ) $monthNo = static::getMonth( $monthNo );

		$ret = static::$months[ $monthNo - 1 ];
		$ret = '__' . $ret . '__';
		return $ret;
	}

	public static function isFullDay( $dt1, $dt2 )
	{
		$ret = false;
		if( '0000' !== substr($dt1, -4) ){
			return $ret;
		}
		if( '2400' !== substr($dt2, -4) ){
			return $ret;
		}
		$ret = true;
		return $ret;
	}

	public function formatFull( $dateTimeDb )
	{
		if( ! $dateTimeDb ){
			$ret = '__N/A__';
			return $ret;
		}

		$ret = $this->formatWeekday( $dateTimeDb ) . ', ' . $this->formatDate( $dateTimeDb ) . ' ' . $this->formatTime( $dateTimeDb );
		return $ret;
	}

	public function formatDayMonth( $dt )
	{
		static $ret = [];
		if( isset($ret[$dt]) ){
			return $ret[$dt];
		}

		$dateFormat = $this->dateFormat;

	// skip year in format
		$posY = strpos( $dateFormat, 'Y' );
		if( false !== $posY ){
			if( 0 == $posY ){
				$dateFormat = substr_replace( $dateFormat, '', $posY, 2 );
			}
			else {
				$dateFormat = substr_replace( $dateFormat, '', $posY - 1, 2 );
			}
		}

		$ret[$dt] = $this->formatDate( $dt, $dateFormat );
		return $ret[$dt];
	}

	public function formatDateFull( $dateTimeDb )
	{
		$ret = $this->formatWeekday( $dateTimeDb ) . ', ' . $this->formatDate( $dateTimeDb );
		return $ret;
	}

	public function formatDateFullWithRelative( $dateTimeDb )
	{
		$ret = $this->formatDateFull( $dateTimeDb );

		$d = $this->getDate( $dateTimeDb );
		$today = $this->getDate( $this->getNow() );
		if( $today == $d ){
			$ret .= ' (' . '__Today__' . ')';
		}

		return $ret;
	}

	public function formatHour( $dateTimeDb, $format = null )
	{
		$ret = $this->formatTime( $dateTimeDb, $format );
		$pos = strpos( $ret, ':' );
		if( false !== $pos ){
			$ret = substr_replace( $ret, '', $pos, 3 );
		}
		return $ret;
	}

	public function formatMinute( $dateTimeDb, $format = null )
	{
		$ret = substr( $dateTimeDb, -2 );
		return $ret;
	}

	public function formatTime( $dateTimeDb, $format = null )
	{
		static $cache = [];

		if( null === $format ){
			$format = $this->timeFormat;
		}

		$h = static::getHour( $dateTimeDb );
		$m = static::getMinute( $dateTimeDb );

		$cacheKey = $h . '-' . $m . '-' . $format;

		if( isset($cache[$cacheKey]) ){
			return $cache[$cacheKey];
		}

		$t = static::t();
		$t->setTime( $h, $m );

		if( '12short' === $format ){
			$ret = $t->format( 'g:ia' );
			$ret = str_replace( ':00', '', $ret );
		}
		elseif( '12xshort' === $format ){
			$ret = $t->format( 'g:ia' );
			$ret = str_replace( 'am', 'a', $ret );
			$ret = str_replace( 'pm', 'p', $ret );
			$ret = str_replace( ':00', '', $ret );
		}
		elseif( '24short' === $format ){
			$ret = $t->format( 'H:i' );
			$ret = preg_replace( '/0(\d\:)/', '${1}', $ret );
			$ret = str_replace( ':00', '', $ret );
		}
		else {
			$ret = $t->format( $format );
		}

		$cache[$cacheKey] = $ret;

		return $ret;
	}

	public function formatTimeRangeInDay( $secStart, $secEnd, $format = null )
	{
		$ret = static::formatTimeInDay( $secStart, $format ) . ' - ' . static::formatTimeInDay( $secEnd, $format );
		return $ret;
	}

	public function formatTimeInDay( $seconds, $format = null )
	{
		$dateTimeDb = static::fromDateSeconds( '20230223', $seconds );
		return $this->formatTime( $dateTimeDb, $format );
	}

	public function secondToTimeInDayDbTable()
	{
		$ret = [];

		$hours = [
			0 => '00', 1 => '01', 2 => '02', 3 => '03', 4 => '04', 5 => '05',
			6 => '06', 7 => '07', 8 => '08', 9 => '09', 10 => '10', 11 => '11',
			12 => '12', 13 => '13', 14 => '14', 15 => '15', 16 => '16', 17 => '17',
			18 => '18', 19 => '19', 20 => '20', 21 => '21', 22 => '22', 23 => '23'
		];
		$mins = [
			0 => '00', 5 => '05', 10 => '10', 15 => '15', 20 => '20', 25 => '25',
			30 => '30', 35 => '35', 40 => '40', 45 => '45', 50 => '50', 55 => '55',
		];

		foreach( array_keys($hours) as $h ){
			foreach( array_keys($mins) as $m ){
				$ret[ $h*60*60 + $m*60 ] = $hours[$h] . $mins[$m];
			}
		}

		return $ret;
	}

	public static function formatTimeInDayDb( $seconds )
	{
		static $ret = [];

		if( ! $ret ){
			$hours = [
				0 => '00', 1 => '01', 2 => '02', 3 => '03', 4 => '04', 5 => '05',
				6 => '06', 7 => '07', 8 => '08', 9 => '09', 10 => '10', 11 => '11',
				12 => '12', 13 => '13', 14 => '14', 15 => '15', 16 => '16', 17 => '17',
				18 => '18', 19 => '19', 20 => '20', 21 => '21', 22 => '22', 23 => '23'
			];
			$mins = [
				0 => '00', 5 => '05', 10 => '10', 15 => '15', 20 => '20', 25 => '25',
				30 => '30', 35 => '35', 40 => '40', 45 => '45', 50 => '50', 55 => '55',
			];

			foreach( array_keys($hours) as $h ){
				foreach( array_keys($mins) as $m ){
					$ret[ $h*60*60 + $m*60 ] = $hours[$h] . $mins[$m];
				}
			}
		}

		if( ! isset($ret[$seconds]) ){
			$remain = $seconds;

			$h = floor( $remain / (60 * 60) );
			$remain = $remain - $h * (60 * 60);
			$hh = str_pad( $h, 2, 0, STR_PAD_LEFT );

			$m = floor( $remain / 60 );
			$mm = str_pad( $m, 2, 0, STR_PAD_LEFT );

			$ret[$seconds] = $hh . $mm;
		}

		return $ret[$seconds];
	}

	public function formatDate( $dateTimeDb, $format = null )
	{
		$t = static::t();
		if( null === $format ){
			$format = $this->dateFormat;
		}

		$t->setDate( static::getYear($dateTimeDb), static::getMonth($dateTimeDb), static::getDay($dateTimeDb) );
		$ret = $t->format( $format );

	// replace English months to localized ones
		foreach( static::$months as $m ){
			$from = $m;
			$to = '__' . $m . '__';
			$ret = str_replace( $from, $to, $ret );
		}

		return $ret;
	}

	public static function modify( $dateTimeDb, $modify )
	{
		$t = static::t();
		$t->setDate( static::getYear($dateTimeDb), static::getMonth($dateTimeDb), static::getDay($dateTimeDb) );
		$t->setTime( static::getHour($dateTimeDb), static::getMinute($dateTimeDb) );

		$t->modify( $modify );
		$ret = static::_convert( $t );

		return $ret;
	}

	public static function addSeconds( $dateTimeDb, $duration )
	{
		static $cache = [];

		$key = $dateTimeDb;
		$key .= ( $duration > 0 ) ? '+' . $duration : '-' . (-$duration);

		if( isset($cache[$key]) ){
			return $cache[$key];
		}

		$t = static::t();
		$t->setDate( static::getYear($dateTimeDb), static::getMonth($dateTimeDb), static::getDay($dateTimeDb) );
		$t->setTime( static::getHour($dateTimeDb), static::getMinute($dateTimeDb) );

		if( $duration > 0 ){
			$t->modify( '+ ' . $duration . ' seconds' );
		}
		else {
			$t->modify( '- ' . (- $duration) . ' seconds' );
		}

		$ret = static::_convert( $t );
		$cache[$key] = $ret;

		return $ret;
	}

// maxMeasure can be d, h, m
	public static function formatDurationVerbose( $seconds, $maxMeasure = 'd', $minMeasure = null )
	{
		static $cache = [];

		$cacheKey = (string) $seconds;
		if( isset($cache[$cacheKey]) ){
			return $cache[$cacheKey];
		}

		$measures = array( 'd' => 'd', 'h' => 'h', 'm' => 'm' );
		if( 'd' === $maxMeasure ){
		}
		if( 'h' === $maxMeasure ){
			unset( $measures['d'] );
		}
		if( 'm' === $maxMeasure ){
			unset( $measures['d'] ); unset( $measures['h'] );
		}

		if( 'd' === $minMeasure ){
			unset( $measures['h'] ); unset( $measures['m'] );
		}
		if( 'h' === $minMeasure ){
			unset( $measures['m'] );
		}
		if( 'm' === $minMeasure ){
		}

		$days = isset($measures['d']) ? floor( $seconds / (24 * 60 * 60) ) : 0;
		$remain = $seconds - $days * (24 * 60 * 60);
		$hours = isset($measures['h']) ? floor( $remain / (60 * 60) ) : 0;
		$remain = $remain - $hours * (60 * 60);
		$minutes = isset($measures['m']) ? floor( $remain / 60 ) : 0;

		$ret = [];

		if( 'd' === $minMeasure ){
			$days = $days + 1;
		}
		if( 'h' === $minMeasure ){
			$hours = $hours + 1;
		}

		if( $days ){
			$daysView = $days;
			$daysView = $daysView . '' . '__d__';
			$ret[] = $daysView;
		}

		if( $hours ){
			$hoursView = $hours;
			$hoursView = $hoursView . '' . '__h__';
			$ret[] = $hoursView;
		}

		if( $minutes ){
			$minutesView = sprintf( '%02d', $minutes );
			$minutesView = $minutesView . '' . '__m__';
			$ret[] = $minutesView;
		}

		$ret = join( ' ', $ret );

		$cache[ $cacheKey ] = $ret;

		return $ret;
	}

	public static function formatDuration( $seconds )
	{
		static $cache = [];
		if( isset($cache[$seconds]) ){
			return $cache[$seconds];
		}

		$hours = floor( $seconds / (60 * 60) );
		$remain = $seconds - $hours * (60 * 60);
		$minutes = floor( $remain / 60 );

		$hoursView = $hours;
		$minutesView = sprintf( '%02d', $minutes );

		$ret = $hoursView . ':' . $minutesView;
		// $return = gmdate( "H:i", $this->getDuration() );

		$cache[$seconds] = $ret;

		return $ret;
	}

	public static function formatDurationNum( $seconds, $precision = 2 )
	{
		static $cache = [];
		if( isset($cache[$seconds]) ){
			return $cache[$seconds];
		}

		$hours = floor( $seconds / (60 * 60) );
		$remain = $seconds - $hours * (60 * 60);
		$minutes = floor( $remain / 60 );
		$hoursView = $hours;

		$minutesNum = (100 * $minutes) / 60;
		if( $precision ){
			$minutesView = sprintf( '%0' . $precision. 'd', $minutesNum );
			$ret = $hoursView . '.' . $minutesView;
		}
		else {
			$ret = $hoursView;
		}

		// $return = gmdate( "H:i", $this->getDuration() );

		$cache[$seconds] = $ret;

		return $ret;
	}

	public function getDateMatrix( $d1, $d2, $inRowRange, $inRowQty = 1 )
	{
		$ret = [];

		if( 'month' == $inRowRange ){
			$rex = $this->getDate( $this->getStartMonth($d1) );

			$maxCountInRow = 0;
			while( $rex <= $d2 ){
				$row = [];
				$rexEndRow = ( $inRowQty > 1 ) ? $this->modify( $rex, '+' . ($inRowQty - 1) . ' month' ) : $rex;
				$rexEndRow = $this->getDate( $this->getEndMonth($rexEndRow) );
				while( $rex <= $rexEndRow ){
					$row[] = ( $rex >= $d1 ) && ( $rex <= $d2 ) ? $rex : null;
					$rex = $this->getNextDate( $rex );
				}
				$ret[] = $row;
				if( count($row) > $maxCountInRow ){
					$maxCountInRow = count($row);
				}
			}

		// pad the last rows to max count
			foreach( array_keys($ret) as $ii ){
				while( count($ret[$ii]) < $maxCountInRow ) $ret[$ii][] = null; 
			}
		}
		elseif( 'week' == $inRowRange ){
			$weekMatrix = $this->getWeekMatrix( $d1, $d2, false );

			$row = [];
			$weekCount = 0;
			foreach( $weekMatrix as $weekRow ){
				foreach( $weekRow as $d ){
					$row[] = $d;
				}
				$weekCount++;
				if( $weekCount == $inRowQty ){
					$ret[] = $row;
					$row = [];
					$weekCount = 0;
				}
			}

			if( $row ){
				// pad the last row?
				if( $ret ){
					$countFirstRow = count( current($ret) );
					while( count($row) < $countFirstRow ){
						$row[] = null;
					}
				}
				$ret[] = $row;
			}
		}
		elseif( 'day' == $inRowRange ){
			$listDate = static::getDates( $d1, $d2 );
			$listDate = array_keys( $listDate );
			$countRow = ceil( count($listDate) / $inRowQty );
			for( $r = 0; $r < $countRow; $r++ ){
				for( $c = 0; $c < $inRowQty; $c++ ){
					$ret[ $r ][ $c ] = $listDate[ $r*$inRowQty + $c ] ?? null;
				}
			}
		}
		else {
			echo 'unknown inrow period "' . esc_html($inRowRange) . '"<br>';
		}

		return $ret;
	}

	public function getMonthMatrix( $currentDate, $overlap = false, $skipWeekdays = [] )
	{
		$matrix = [];
		$currentMonthDay = 0;

		$thisMonth = static::getMonth( $currentDate );

		$startDate = static::getStartMonth($currentDate);
		$startDate = static::getDate( static::getStartMonth($currentDate) );

		if( $overlap ){
			$startDate = static::getDate( static::getStartWeek($startDate) );
		}

		$endDate = static::getDate( static::getEndMonth($currentDate) );
		if( $overlap ){
			$endDate = static::getDate( static::getEndWeek($endDate) );
		}

		$rexDate = $startDate;
		if( ! $overlap ){
			$rexDate = static::getDate( static::getStartWeek($startDate) );
		}

		while( $rexDate <= $endDate ){
			$week = [];
			$weekSet = false;
			$thisWeekStart = $rexDate;

			for( $weekDay = 0; $weekDay <= 6; $weekDay++ ){
				$setDate = $rexDate;
				$thisWeekday = static::getWeekday( $setDate );

				if( ! $overlap ){
					if( ($rexDate > $endDate) OR ($rexDate < $startDate) ){
						$setDate = null;
					}
				}

				if( (! $skipWeekdays) OR (! in_array($thisWeekday, $skipWeekdays)) ){
					if( NULL !== $setDate ){
						$rexMonth = static::getMonth( $setDate );
						if( ! $overlap ){
							if( $rexMonth != $thisMonth ){
								$setDate = null;
							}
						}
					}

					$week[ $thisWeekday ] = $setDate;
					if( null !== $setDate ){
						$weekSet = true;
					}
				}

				$rexDate = static::getNextDate( $rexDate );
			}

			if( $weekSet )
				$matrix[ $thisWeekStart ] = $week;
		}

		return $matrix;
	}

	public function getManyMonthMatrix( $startDate, $endDate )
	{
		$ret = [];

		$rex = static::getDate( static::getStartMonth($startDate) );
		while( $rex <= $endDate ){
			$thisMonth = substr( $rex, 0, 6 );
			$ret[ $thisMonth ] = $this->getMonthMatrix( $rex );

			$rex = static::modify( $rex, '+1 month');
			$rex = static::getDate( $rex );
		}

		return $ret;
	}

	public function getMonthWeekMatrix( $startDate, $endDate, $overlap = false, $fullMonth = false )
	{
		$ret = [];

		if( $fullMonth ){
			$startDate = static::getDate( static::getStartMonth($startDate) );
			$endDate = static::getDate( static::getEndMonth($endDate) );
		}

		$weekMatrix = $this->getWeekMatrix( $startDate, $endDate, $overlap );
		foreach( $weekMatrix as $weekStart => $weekDateList ){
			$count = [];

			$thisThisMonth = null;
			foreach( $weekDateList as $d ){
				if( ! $d ) continue;
				$thisMonth = substr( $d, 0, 6 );
				$count[$thisMonth] = isset($count[$thisMonth]) ? $count[$thisMonth] + 1 : 1;
				if( $count[$thisMonth] > 3 ){
					$thisThisMonth = $thisMonth;
					break;
				}
			}

			if( ! $thisThisMonth ){
				$thisThisMonth = $thisMonth;
			}

			$thisThisMonth = (int) $thisThisMonth;
			if( ! isset($ret[$thisThisMonth]) ){
				$ret[$thisThisMonth] = [];
			}

			$weekNo = (int) $this->getWeekNo( $weekStart );
			$ret[ $thisThisMonth ][ $weekNo ] = $weekDateList;
		}

		return $ret;
	}

	public function getWeekMatrix( $startDate, $endDate, $overlap = true )
	{
		$ret = [];

		if( $overlap ){
			$startDate = static::getDate( static::getStartWeek($startDate) );
			$endDate = static::getDate( static::getEndWeek($endDate) );
		}

		// $rexDate = $startDate;
		$rexDate = static::getDate( static::getStartWeek($startDate) );

		while( $rexDate <= $endDate ){
			$week = [];
			$weekSet = false;
			$thisWeekStart = $rexDate;

			for( $weekDay = 0; $weekDay <= 6; $weekDay++ ){
				$setDate = $rexDate;
				$thisWeekday = static::getWeekday( $setDate );

				if( ($rexDate > $endDate) OR ($rexDate < $startDate) ){
					$setDate = null;
				}

				$week[ $thisWeekday ] = $setDate;
				if( null !== $setDate ){
					$weekSet = true;
				}

				$rexDate = static::getNextDate( $rexDate );
			}

			if( $weekSet ){
				// $k = $withWeekNo ? $this->getWeekNo( $thisWeekStart ) : $thisWeekStart;
				$k = $thisWeekStart;
				$ret[ $k ] = $week;
			}
		}

		return $ret;
	}

	public static function countDays( $dt1, $dt2 )
	{
		$dt1 = static::getStartDay( $dt1 );
		$dt2 = static::getEndDay( $dt2 );

		$duration = static::getDuration( $dt1, $dt2 );
		$ret = ceil( $duration / (24*60*60) );
		return $ret;
	}

	public static function getDuration( $dateTimeDb1, $dateTimeDb2 )
	{
		static $cache = [];

		if( $dateTimeDb1 == $dateTimeDb2 ){
			return 0;
		}

		$key = $dateTimeDb1 . '-' . $dateTimeDb2;
		if( isset($cache[$key]) ){
			return $cache[$key];
		}

		$t = static::t();

		$t->setDate( static::getYear($dateTimeDb1), static::getMonth($dateTimeDb1), static::getDay($dateTimeDb1) );
		$t->setTime( static::getHour($dateTimeDb1), static::getMinute($dateTimeDb1) );
		$timestamp1 = $t->getTimestamp();

		$t->setDate( static::getYear($dateTimeDb2), static::getMonth($dateTimeDb2), static::getDay($dateTimeDb2) );
		$t->setTime( static::getHour($dateTimeDb2), static::getMinute($dateTimeDb2) );
		$timestamp2 = $t->getTimestamp();

		$ret = abs( $timestamp2 - $timestamp1 );

		$cache[$key] = $ret;

		return $ret;
	}

	public function formatRange( $dt1, $dt2 )
	{
		if( static::isStartDay($dt1) && static::isEndDay($dt2) ){
			$ret = $this->formatDateRange( $dt1, $dt2 );
		}
		else {
			$d1 = static::getDate( $dt1 );
			$d2 = static::getDate( $dt2 );

			if( $d1 == $d2 ){
				$ret = $this->formatDateFull( $dt1 ) . ' ' . $this->formatTime( $dt1 ) . ' - ' . $this->formatTime( $dt2 );
			}
			else {
				$ret = $this->formatFull( $dt1 ) . ' - ' . $this->formatFull( $dt2 );
			}
		}

		return $ret;
	}

	public function formatDateRange( $date1, $date2 )
	{
		$ret = [];
		$skip = [];

		$date1 = static::getDate( $date1 );
		$date2 = static::getDate( $date2 );

		if( $date1 == $date2 ){
			$ret = $this->formatDate( $date1 );
			return $ret;
		}

		$year1 = static::getYear( $date1 );
		$year2 = static::getYear( $date2 );
		$month1 = static::getMonth( $date1 );
		$month2 = static::getMonth( $date2 );

	// WHOLE YEAR?
		if( ( $date1 == static::getDate(static::getStartYear($date1)) ) && ( $date2 == static::getDate(static::getEndYear($date2)) ) ){
			$ret = ( $year1 == $year2 ) ? $year1 : $year1 . ' - ' . $year2;
			return $ret;
		}

	// WHOLE MONTH?
		// if( ( $date1 == static::getDate(static::getStartMonth($date1)) ) && ( $date2 == static::getDate(static::getEndMonth($date2)) ) ){
			// if( $year1 == $year2 ){
				// if( $month1 == $month2 ){
					// $ret = static::formatMonthName( $month1 ) . ' ' . $year1;
				// }
				// else {
					// $ret = static::formatMonthName( $month1 ) . ' - ' . static::formatMonthName( $month2 ) . ' ' . $year2;
				// }
			// }
			// else {
				// $ret = static::formatMonthName( $month1 ) . ' ' . $year1 . ' - ' . static::formatMonthName( $month2 ) . ' ' . $year2;
			// }
			// return $ret;
		// }

		// if( ($year1 == $year2) && ( $date1 == static::getDate(static::getStartMonth($date1)) ) && ( $date2 == static::getDate(static::getEndMonth($date2)) ) ){
		if( ( $date1 == static::getDate(static::getStartMonth($date1)) ) && ( $date2 == static::getDate(static::getEndMonth($date2)) ) ){
			if( $month1 == $month2 ){
				$ret = static::formatMonthName( $month1 ) . ' ' . $year1;
			}
			else {
				$ret = static::formatMonthName( $month1 ) . ' - ' . static::formatMonthName( $month2 ) . ' ' . $year2;
			}
			return $ret;
		}

		$dateFormat = $this->dateFormat;
		$dateFormat1 = $dateFormat;
		$dateFormat2 = $dateFormat;

	// skip month in first one
		if( ($year2 == $year1) && ($month2 == $month1) ){
			$tags = [ 'm', 'n', 'M' ];
			foreach( $tags as $tag ){
				$posM = strpos( $dateFormat1, $tag );
				if( $posM !== false )
					break;
			}

			if( false !== $posM ){
				if( 0 == $posM ){
					$dateFormat1 = substr_replace( $dateFormat1, '', $posM, 2 );
				}
				else {
					$dateFormat1 = substr_replace( $dateFormat1, '', $posM - 1, 2 );
				}
			}
		}

	// skip year in first one
		if( $year2 == $year1 ){
			$posY = strpos( $dateFormat1, 'Y' );
			if( false !== $posY ){
				if( 0 == $posY ){
					$dateFormat1 = substr_replace( $dateFormat1, '', $posY, 2 );
				}
				else {
					$dateFormat1 = substr_replace( $dateFormat1, '', $posY - 1, 2 );
				}
			}
		}

		$viewDate1 = $this->formatDate( $date1, $dateFormat1 );
		$viewDate2 = $this->formatDate( $date2, $dateFormat2 );

		$ret = $viewDate1 . ' - ' . $viewDate2;

		return $ret;
	}

	public static function getDayOfWeekOccurenceInMonth( $date )
	{
		$month = static::getMonth( $date );

		$rexMonth = $month;
		$ret = 0;
		while( $rexMonth == $month ){
			$ret++;
			$date = static::modify( $date, '-1 week');
			$rexMonth = static::getMonth( $date );
		}

		return $ret;
	}

	public static function getTimeInDay( $dateTimeDb )
	{
		if( $dateTimeDb < 10000000 ){
			return $dateTimeDb;
		}

		$h = static::getHour( $dateTimeDb );
		$m = static::getMinute( $dateTimeDb );
		$ret = 60 * 60 * $h + 60 * $m;
		return $ret;
	}

	public static function fromTimestamp( $timestamp )
	{
		static $cache = [];
		if( isset($cache[$timestamp]) ){
			return $cache[$timestamp];
		}

		$t = static::t();
		$t->setTimestamp( $timestamp );
		$ret = static::_convert( $t );

		$cache[$timestamp] = $ret;

		return $ret;
	}

	public function findRecurringDateList( $d1, $d2, $recurType, $recurDetail = [] )
	{
		$ret = [];

		$rex = $d1;
		while( $rex <= $d2 ){
			$ok = true;

			if( 'daily' == $recurType ){
				$ok = true;
			}

			if( 'weekly' == $recurType ){
				$wkd = static::getWeekday( $rex );
				$ok = in_array( $wkd, $recurDetail ) ? true : false;
			}

			if( $ok ){
				$ret[ $rex ] = $rex;
			}

			$rex = static::getNextDate( $rex );
		}

		return $ret;
	}

	public function makeTickList( $timeStart, $timeEnd, $duration, $interval )
	{
		static $cache = [];
		$cacheId = $timeStart . '-' . $timeEnd . '-' . $duration . '-' . $interval;
		if( isset($cache[$cacheId]) ){
			return $cache[$cacheId];
		}

		$ret = [];

		$rexTime = $timeStart;
		$timeEnd = $timeEnd - $duration;
		if( ! $interval ) $interval = $duration;

		while( $rexTime <= $timeEnd ){
			$startAt = static::formatTimeInDayDb( $rexTime );
			$endAt = static::formatTimeInDayDb( $rexTime + $duration );
			$ret[ $startAt ] = $endAt;
			$rexTime += $interval;
		}

		$cache[ $cacheId ] = $ret;

		return $ret;
	}

	public function makeDateTickList( $date, $timeStart, $timeEnd, $duration, $interval )
	{
		static $cache = [];
		$cacheId = $date . '-' . $timeStart . '-' . $timeEnd . '-' . $duration . '-' . $interval;
		if( isset($cache[$cacheId]) ){
			return $cache[$cacheId];
		}

		$ret = [];

		$rexTime = $timeStart;
		$timeEnd = $timeEnd - $duration;
		if( ! $interval ) $interval = $duration;

		while( $rexTime <= $timeEnd ){
			$timeStartAt = static::formatTimeInDayDb( $rexTime );
			$timeEndAt = static::formatTimeInDayDb( $rexTime + $duration );

			$startAt = $date . $timeStartAt;
			$endAt = $date . $timeEndAt;

			$ret[ $startAt ] = $endAt;
			$rexTime += $interval;
		}

		$cache[ $cacheId ] = $ret;

		return $ret;
	}

	public function getTimezone()
	{
		$ret = $this->timezone ? $this->timezone : $this->getDefaultTimezone();
		return $ret;
	}

	public function getTimezones()
	{
		$ret = [];

		$listSkipStart = [ 'Brazil/', 'Canada/', 'Chile/', 'Etc/', 'Mexico/', 'US/' ];
		$listSkipStart = [ 'Etc/' ];
		// $listSkipStart = [];

		if( defined('DateTimeZone::ALL_WITH_BC') )
			$timezones = timezone_identifiers_list( \DateTimeZone::ALL_WITH_BC );
		else
			$timezones = timezone_identifiers_list();

		reset( $timezones );
		foreach( $timezones as $tz ){
			if( false === strpos($tz, "/") )
				continue;

			$skipIt = false;
			reset( $listSkipStart );
			foreach( $listSkipStart as $skip ){
				if( substr($tz, 0, strlen($skip)) == $skip ){
					$skipIt = true;
					break;
				}
			}
			if( $skipIt )
				continue;

			$tzTitle = $this->timezoneTitle( $tz );
			$ret[ $tz ] = $tzTitle;
		}

		return $ret;
	}

	public function timezoneTitle( $tz, $showOffset = FALSE )
	{
		if( is_array($tz) )
			$tz = $tz[0];

		$tzobj = new \DateTimeZone( $tz );
		$dtobj = new \DateTime();
		$dtobj->setTimezone( $tzobj );

		if( $showOffset ){
			$offset = $tzobj->getOffset( $dtobj );
			$offsetString = 'GMT';
			$offsetString .= ($offset >= 0) ? '+' : '';
			$offsetString = $offsetString . ( $offset/(60 * 60) );
			$ret = $tz . ' (' . $offsetString . ')';
		}
		else {
			$ret = $tz;
		}

		return $ret;
	}

	public function getDefaultTimezone()
	{
		$ret = date_default_timezone_get();

		if( defined('WPINC') ){
			$tz = get_option('timezone_string');
			if( ! strlen($tz) ){
				$offset = get_option('gmt_offset');
				if( $offset ){
					$tz = 'Etc/GMT';
					if( $offset > 0 ){
						$tz .= '+' . $offset;
					}
					else {
						$tz .= '-' . -$offset;
					}
				}
			}

			if( strlen($tz) ){
				$ret = $tz;
			}
		}

		return $ret;
	}
}