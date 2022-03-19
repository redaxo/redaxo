<?php namespace Clockwork\Request;

use Clockwork\Helpers\Serializer;
use Clockwork\Helpers\StackTrace;
use Clockwork\Helpers\StackFilter;

// Data structure representing a log with timestamped messages
class Log
{
	// Array of logged messages
	public $messages = [];

	// Create a new log, optionally with existing messages
	public function __construct($messages = [])
	{
		$this->messages = $messages;
	}

	// Log a new message, with a level and context, context can be used to override serializer defaults,
	// $context['trace'] = true can be used to force collecting a stack trace
	public function log($level = LogLevel::INFO, $message = null, array $context = [])
	{
		$trace = $this->hasTrace($context) ? $context['trace'] : StackTrace::get()->resolveViewName();

		$this->messages[] = [
			'message'   => (new Serializer($context))->normalize($message),
			'exception' => $this->formatException($context),
			'context'   => $this->formatContext($context),
			'level'     => $level,
			'time'      => microtime(true),
			'trace'     => (new Serializer(! empty($context['trace']) ? [ 'traces' => true ] : []))->trace($trace)
		];
	}

	public function emergency($message, array $context = [])
	{
		$this->log(LogLevel::EMERGENCY, $message, $context);
	}

	public function alert($message, array $context = [])
	{
		$this->log(LogLevel::ALERT, $message, $context);
	}

	public function critical($message, array $context = [])
	{
		$this->log(LogLevel::CRITICAL, $message, $context);
	}

	public function error($message, array $context = [])
	{
		$this->log(LogLevel::ERROR, $message, $context);
	}

	public function warning($message, array $context = [])
	{
		$this->log(LogLevel::WARNING, $message, $context);
	}

	public function notice($message, array $context = [])
	{
		$this->log(LogLevel::NOTICE, $message, $context);
	}

	public function info($message, array $context = [])
	{
		$this->log(LogLevel::INFO, $message, $context);
	}

	public function debug($message, array $context = [])
	{
		$this->log(LogLevel::DEBUG, $message, $context);
	}

	// Merge another log instance into the current log
	public function merge(Log $log)
	{
		$this->messages = array_merge($this->messages, $log->messages);

		return $this;
	}

	// Sort the log messages by timestamp
	public function sort()
	{
		usort($this->messages, function ($a, $b) { return $a['time'] * 1000 - $b['time'] * 1000; });
	}

	// Get all messages as an array
	public function toArray()
	{
		return $this->messages;
	}

	// Format message context, removes exception and trace if we are serializing them
	protected function formatContext($context)
	{
		if ($this->hasException($context)) unset($context['exception']);
		if ($this->hasTrace($context)) unset($context['trace']);

		return (new Serializer)->normalize($context);
	}

	// Format exception if present in the context
	protected function formatException($context)
	{
		if ($this->hasException($context)) {
			return (new Serializer)->exception($context['exception']);
		}
	}

	// Check if context has serializable trace
	protected function hasTrace($context)
	{
		return ! empty($context['trace']) && $context['trace'] instanceof StackTrace && empty($context['raw']);
	}

	// Check if context has serializable exception
	protected function hasException($context)
	{
		return ! empty($context['exception']) && $context['exception'] instanceof \Exception && empty($context['raw']);
	}
}
