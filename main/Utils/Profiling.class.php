<?php
/**
 * Замер скорости исполнения отдельных блоков кода
 * @author Михаил Кулаковский <m@klkvsk.ru>
 * @date 09.12.13
 */

class Profiling {
	protected static $history = array();
	protected static $historyEnabled = true;

	protected $timeStart = null;
	protected $timeEnd = null;
	protected $tags = array();
	protected $info = null;
	protected $trace = null;

	public static function create($tags = array(), $info = null) {
		$self = new static();
		if (!is_array($tags)) {
			$tags = array($tags);
		}
		$self->tags = $tags;
		$self->info = $info;
		$backtrace = debug_backtrace();
		array_shift($backtrace);
		$self->trace = $backtrace;
		return $self;
	}

	/**
	 * @param $tag
	 * @return self[]
	 */
	public static function getHistory($tag) {
		if (isset(self::$history[$tag])) {
			return self::$history[$tag];
		}
		return array();
	}

	public static function isHistoryEnabled() {
		return self::$historyEnabled;
	}

	public static function setHistoryEnabled($bool) {
		self::$historyEnabled = ($bool === true);
	}

	public static function getTotalTime($tag) {
		$time = 0;
		foreach (self::getHistory($tag) as $profiling) {
			$time += $profiling->getTimeMs();
		}
		return $time;
	}

	public function begin() {
		$this->timeStart = microtime(true);
		return $this;
	}

	public function end($saveToHistory = null) {
		$this->timeEnd = microtime(true);

		if (is_null($saveToHistory)) {
			$saveToHistory = self::isHistoryEnabled();
		}

		if ($saveToHistory) {
			foreach ($this->getTags() as $tag) {
				if (!isset(self::$history[$tag])) {
					self::$history[$tag] = array();
				}
				self::$history[$tag] []= $this;
			}
		}

		return $this;
	}

	public function getTime() {
		if ($this->timeStart == null) {
			return -INF;
		} else if ($this->timeEnd == null) {
			return +INF;
		}
		return $this->timeEnd - $this->timeStart;
	}

	public function getTimeMs(){
		return round($this->getTime() * 1000, 3);
	}

	public function getTimeStart($format = 'Y-m-d H:i:s.u') {
		$time = $this->timeStart;
		if ($time === null) {
			return $format ? 'never' : 0;
		}
		if( strpos($time,'.')===false ) {
			$time .= '.0';
		}
		if ($format) {
			$time = DateTime::createFromFormat('U.u', $time)->format($format);
		}
		return $time;
	}

	public function getTimeEnd($format = 'Y-m-d H:i:s.u') {
		$time = $this->timeEnd;
		if ($time === null) {
			return $format ? 'never' : 0;
		}
		if( strpos($time,'.')===false ) {
			$time .= '.0';
		}
		if ($format) {
			$time = DateTime::createFromFormat('U.u', $time)->format($format);
		}
		return $time;
	}

	public function getTags() {
		return $this->tags;
	}

	public function getInfo(){
		return $this->info;
	}

	public function getTrace() {
		return $this->trace;
	}

	public function getTraceAsString() {
		return implode(PHP_EOL, array_map(
			function ($bt) {
				$string = '';
				if (isset($bt['file'])) {
					$string .= basename($bt['file']) . '(' . $bt['line'] . '): ';
				}
				if (isset($bt['class'])) {
					$string .= $bt['class'] . $bt['type'];
				}
				$string .= $bt['function'];
				$string .= '(';
				if (isset($bt['args'])) {
					$string .= implode(', ', array_map(function ($arg) {
						if (is_null($arg)) return 'NULL';
						else if (is_resource($arg)) return '{resource}';
						else if (is_array($arg)) return '{array(' . count($arg) .')}';
						else if (is_object($arg)) return '{' . get_class($arg) .'}';
						else if (is_scalar($arg)) {
							if (is_string($arg) && strlen($arg) > 64) {
								$arg = substr($arg, 0, 64) . '...';
							}
							return var_export($arg, true);
						}
						else if (is_callable($arg)) return '{closure}';
						else return '{unknown}';
					}, $bt['args']));
				}
				$string .= ')';
				return $string;
			},
			$this->getTrace()
		));
	}

    /**
     * @param null $info
     * @return $this
     */
    public function setInfo($info) {
        $this->info = $info;
        return $this;
    }
} 