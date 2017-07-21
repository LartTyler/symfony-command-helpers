<?php
	namespace DaybreakStudios\Utility\SymfonyCommandHelpers;

	use DaybreakStudios\Utility\DateTimeHelpers\Parser;
	use Symfony\Component\Console\Command\Command;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Input\InputOption;

	final class CommandUtil {
		/**
		 * Converts arguments and options present in an InputInterface into a string that can be used to rerun the
		 * command.
		 *
		 * @param InputInterface $input
		 *
		 * @return string
		 */
		public static function getCommandSignature(InputInterface $input) {
			$sig = '';

			foreach ($input->getArguments() as $value)
				$sig .= ' ' . $value;

			foreach ($input->getOptions() as $key => $value)
				if (!$value)
					continue;
				else if (is_array($value))
					foreach ($value as $item)
						$sig .= ' ' . self::buildOption($key, $item);
				else
					$sig .= ' ' . self::buildOption($key, $value);

			return trim($sig);
		}

		/**
		 * @param string $key
		 * @param string $value
		 *
		 * @return string
		 */
		public static function buildOption($key, $value) {
			return sprintf('--%s %s', $key, $value);
		}

		/**
		 * Adds common options to a command for date ranges.
		 *
		 * @param Command $command
		 * @param string  $since
		 * @param string  $before
		 * @param string  $between
		 *
		 * @see CommandUtil::getCommonDateRange()
		 */
		public static function addCommonDateOptions(Command $command, $since = 's', $before = 'b', $between = 't') {
			self::addSinceDateOption($command, $since);
			self::addBeforeDateOption($command, $before);
			self::addBetweenDateOption($command, $between);
		}

		/**
		 * Adds a "since" (date) option to the command.
		 *
		 * @param Command $command
		 * @param string  $alias
		 * @param string  $name
		 */
		public static function addSinceDateOption(Command $command, $alias = 's', $name = 'since') {
			$command->addOption($name, $alias, InputOption::VALUE_REQUIRED,
				'Pull data on or after a timestamp or period');
		}

		/**
		 * Adds a "before" (date) option to the command.
		 *
		 * @param Command $command
		 * @param string  $alias
		 * @param string  $name
		 */
		public static function addBeforeDateOption(Command $command, $alias = 'b', $name = 'before') {
			$command->addOption($name, $alias, InputOption::VALUE_REQUIRED,
				'Pull data on or before a timestamp or period');
		}

		/**
		 * Adds a "between" (date) option to the command.
		 *
		 * @param Command $command
		 * @param string  $alias
		 * @param string  $name
		 */
		public static function addBetweenDateOption(Command $command, $alias = 't', $name = 'between') {
			$command->addOption($name, $alias, InputOption::VALUE_REQUIRED,
				'Pull data between a timestamp or period range (formatted "<start>|<end>"');
		}

		/**
		 * Used in conjunction with CommandUtil::addCommonDateOptions(), this method uses the common option keys to
		 * build default start and end dates.
		 *
		 * @param InputInterface $input
		 *
		 * @return \DateTime[] an array containing the start date at index 0, and the end date at index 1
		 * @see CommandUtil::addCommonDateOptions()
		 */
		public static function getCommonDateRange(InputInterface $input) {
			$start = $end = null;

			if ($since = $input->getOption('since'))
				$start = Parser::getDateTimeFromPeriodOrString($since);
			else if ($before = $input->getOption('before'))
				$end = Parser::getDateTimeFromPeriodOrString($before);
			else if ($between = $input->getOption('between')) {
				$start = Parser::getDateTimeFromPeriodOrString(strtok($between, '|'));
				$end = Parser::getDateTimeFromPeriodOrString(strtok(''));
			}

			if (!$start)
				$start = new \DateTime('today 00:00:00');

			if (!$end)
				$end = new \DateTime();

			return [$start, $end];
		}
	}