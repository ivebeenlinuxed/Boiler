<?php
namespace System\Library\Widget\Complex;

class Gantt extends \Library\Widget\Widget {
	public $result;
	
	const INTERVAL_MINUTE = 0x01;
	const INTERVAL_HOUR = 0x02;
	const INTERVAL_DAY = 0x04;
	const INTERVAL_WEEK = 0x08;
	const INTERVAL_YEAR = 0x10;
	
	public $minute_format = "H:i:s";
	public $hour_format = "H:i";
	public $day_format = "l dS";
	public $week_format = "\Week W (\W\/\B l dS)";
	public $year_format = "Y";
	public $month_format = "F";
	
	
	
	public $major_interval = self::INTERVAL_DAY;
	
	public $major_count = 7;
	
	public $item_tree;
	
	
	public $start_date;
	
	public $previous_href = "";
	public $next_href = "";
	public $now_href = "";
	
	public function __construct() {
		$this->setStart(time());
		$this->item_tree = new \Library\Gantt\Item();
	}
	
	public function setMajorInterval($interval_const) {
		$this->major_interval = $interval_const;
		$this->setStart($this->start_date);
	}
	
	public function setStart($start) {
		$this->start_date = $this->NormalizeMajorDate($start);
		$this->previous_href = "?__date=".$this->getPreviousButtonDate();
		$this->next_href = "?__date=".$this->getNextButtonDate();
		$this->now_href = "?";
	}
	
	public function getMajorFormat() {
		switch ($this->major_interval) {
			case self::INTERVAL_DAY:
				return $this->day_format;
			case self::INTERVAL_HOUR:
				return $this->hour_format;
			case self::INTERVAL_MINUTE:
				return $this->minute_format;
			case self::INTERVAL_WEEK:
				return $this->week_format;
			case self::INTERVAL_YEAR:
				return $this->year_format;
		}
	}
	
	public function getMinorFormat() {
		switch ($this->major_interval) {
			case self::INTERVAL_DAY:
				return $this->hour_format;
			case self::INTERVAL_HOUR:
				return $this->minute_format;
			case self::INTERVAL_MINUTE:
				return $this->minute_format;
			case self::INTERVAL_WEEK:
				return $this->day_format;
			case self::INTERVAL_YEAR:
				return $this->month_format;
		}
	}
	
	public function isMajorToday($date) {
		return $this->NormalizeMajorDate($date) == $this->NormalizeMajorDate(time());
	}
	
	public function dateInDisplayPeriod($date) {
		$date = $this->NormalizeMajorDate($date);
		$start = $this->NormalizeMajorDate($this->start_date);
		$d = $this->getTabDates();
		$last_date = $d[count($d)-1];
		
		if ($date >= $start && $date < $this->getMajorEnd($last_date)) {
			return true;
		}
		
		return false;
	}
	
	public function NormalizeMajorDate($date) {
		switch ($this->major_interval) {
			case self::INTERVAL_MINUTE:
				$start = floor($date/60)*60;
				break;
			case self::INTERVAL_HOUR:
				$start = floor($date/3600)*120;
				break;
			case self::INTERVAL_DAY:
				$start = floor($date/86400)*86400;
				break;
			case self::INTERVAL_WEEK:
				$s = new DateTime("@".$date);
				$i = new DateInterval("P".($s->format("w"))."D");
				$i->invert = 1;
				$date->add($i);
				$start = $s->getTimestamp();
				break;
			case self::INTERVAL_YEAR:
				$s = new \DateTime("@".$date);
				$i = new \DateInterval("P".($s->format("z"))."D");
				$i->invert = 1;
				$s->add($i);
				$s->setTime(0, 0, 0);
				$start = $s->getTimestamp();
				break;
			default:
				throw new Exception("Gantt major interval not recognised");
		}
		return $start;
	}
	
	public function getTabDates() {
		if ($this->major_interval <= $this->minor_interval) {
			throw new Exception("Gantt major interval must be greater than minor interval");
		}
		
		$start = $this->NormalizeMajorDate($this->start_date);
		
		if ($this->major_interval == self::INTERVAL_DAY) {
			$s = new \DateTime("@".$start);
			$i = new \DateInterval("P".($s->format("w")-1)."D");
			$i->invert = 1;
			$s->add($i);
			$start = $s->getTimestamp();
		}
		
		$out = array();
		for ($i=0; $i<$this->major_count; $i++) {
			$out[] = $start;
			switch ($this->major_interval) {
				case self::INTERVAL_MINUTE:
					$start += 60;
					break;
				case self::INTERVAL_HOUR:
					$start += 3600;
					break;
				case self::INTERVAL_DAY:
					$start += 86400;
					break;
				case self::INTERVAL_WEEK:
					$s = new DateTime("@".$start);
					$in = new DateInterval("P7D");
					$date->add($in);
					$start = $s->getTimestamp();
					break;
				case self::INTERVAL_YEAR:
					$s = new \DateTime("@".$start);
					$in = new \DateInterval("P1Y");
					$s->add($in);
					$start = $s->getTimestamp();
					break;
			}
		}
		return $out;
	}
	
	public function getPreviousButtonDate() {
			$start = $this->start_date;
			switch ($this->major_interval) {
				case self::INTERVAL_MINUTE:
					return $start - (60*$this->major_count);
					break;
				case self::INTERVAL_HOUR:
					return $start - (3600*$this->major_count);
					break;
				case self::INTERVAL_DAY:
					return $start - (86400*$this->major_count);
					break;
				case self::INTERVAL_WEEK:
					$s = new DateTime("@".$start);
					$i = new DateInterval("P".(7*$this->major_interval)."D");
					$i->invert = 1;
					$date->add($i);
					$start = $s->getTimestamp();
					break;
				case self::INTERVAL_YEAR:
					$s = new \DateTime("@".$start);
					$i = new \DateInterval("P{$this->major_count}Y");
					$i->invert = 1;
					$s->add($i);
					return $s->getTimestamp();
					break;
			}
		}
		
		public function getMajorEnd($start) {
			switch ($this->major_interval) {
				case self::INTERVAL_MINUTE:
					return $start + (60);
					break;
				case self::INTERVAL_HOUR:
					return $start + (3600);
					break;
				case self::INTERVAL_DAY:
					return $start + (86400);
					break;
				case self::INTERVAL_WEEK:
					$s = new DateTime("@".$start);
					$i = new DateInterval("P7D");
					$i->invert = 1;
					$date->add($i);
					return $s->getTimestamp();
				case self::INTERVAL_YEAR:
					$s = new \DateTime("@".$start);
					$i = new \DateInterval("P1Y");
					$s->add($i);
					return $s->getTimestamp();
			}
		}
	
	public function getNextButtonDate() {
			$start = $this->start_date;
			switch ($this->major_interval) {
				case self::INTERVAL_MINUTE:
					return $start + (60*$this->major_count);
					break;
				case self::INTERVAL_HOUR:
					return $start + (3600*$this->major_count);
					break;
				case self::INTERVAL_DAY:
					return $start + (3600*24*($this->major_count));
					break;
				case self::INTERVAL_WEEK:
					$s = new DateTime("@".$start);
					$i = new DateInterval("P".(7*$this->major_interval)."D");
					$date->add($i);
					return $s->getTimestamp();
					break;
				case self::INTERVAL_YEAR:
					$s = new \DateTime("@".$start);
					$i = new \DateInterval("P".$this->major_count."Y");
					$s->add($i);
					return $s->getTimestamp();
					break;
			}
		
	}
	
	public function getMinorDates($major_interval) {
		$start = $this->NormalizeMajorDate($this->start_date);
		
		
		$out = array();
		for ($i=0; $i<$this->getMinorIntervalCount(); $i++) {
			$out[] = $major_interval;
			switch ($this->major_interval) {
				case self::INTERVAL_MINUTE:
					$major_interval += 60;
				case self::INTERVAL_HOUR:
					$major_interval += 60;
					break;
				case self::INTERVAL_DAY:
					$major_interval += (3600);
					break;
				case self::INTERVAL_WEEK:
					$major_interval = (3600*24);
					break;
				case self::INTERVAL_YEAR:
					$s = new \DateTime("@".$major_interval);
					$in = new \DateInterval("P1M");
					$s->add($in);
					$major_interval = $s->getTimestamp();
					break;
			}
		}
		return $out;
	}
	
	public function getMinorIntervalCount() {
		switch ($this->major_interval) {
			case self::INTERVAL_MINUTE:
				return 60;
			case self::INTERVAL_HOUR:
				return 60;
				break;
			case self::INTERVAL_DAY:
				return 24;
				break;
			case self::INTERVAL_WEEK:
				return 7;
				break;
			case self::INTERVAL_YEAR:
				return 12;
				break;
		}
	}
	
	public function getSnapSeconds() {
		switch ($this->major_interval) {
			case self::INTERVAL_MINUTE:
				return 60;
			case self::INTERVAL_HOUR:
				return 60;
				break;
			case self::INTERVAL_DAY:
				return 15*60;
				break;
			case self::INTERVAL_WEEK:
				return 7*2;
				break;
			case self::INTERVAL_YEAR:
				return 12;
				break;
		}
	}
	
	public function Render() {
		\Core\Router::loadView("widget/complex/gantt", array("controller"=>$this));
	}
}
