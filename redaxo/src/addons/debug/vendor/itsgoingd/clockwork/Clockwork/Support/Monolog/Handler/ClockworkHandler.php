<?php namespace Clockwork\Support\Monolog\Handler;

use Clockwork\Request\Log as ClockworkLog;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

// Stores messages in a Clockwork log instance
// DEPRECATED Moved to Clockwork\Support\Monolog\Monolog\ClockworkHandler, will be removed in Clockwork 6
class ClockworkHandler extends AbstractProcessingHandler
{
	protected $clockworkLog;

	public function __construct(ClockworkLog $clockworkLog)
	{
		parent::__construct();

		$this->clockworkLog = $clockworkLog;
	}

	protected function write(array $record)
	{
		$this->clockworkLog->log($record['level'], $record['message']);
	}
}
