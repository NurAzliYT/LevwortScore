<?php
declare(strict_types = 1);

namespace NurAzliYT\LevwortScore\utils;

use NurAzliYT\LevwortScore\LevwortScore;
use NurAzliYT\LevwortScore\LevwortScoreSettings;
use JackMD\ConfigUpdater\ConfigUpdater;
use Jackmd\scorefactory\ScoreFactory;
use JackMD\UpdateNotifier\UpdateNotifier;
use function preg_match_all;
use function preg_quote;

class Utils{

	private static $REGEX = "";

	/**
	 * Massive shout-out to Cortex/Marshall for this bit of code
	 * used from HRKChat
	 */
	private static function REGEX(): string{
		if(self::$REGEX === ""){
			self::$REGEX = "/(?:" . preg_quote("{") . ")((?:[A-Za-z0-9_\-]{2,})(?:\.[A-Za-z0-9_\-]+)+)(?:" . preg_quote("}") . ")/";
		}

		return self::$REGEX;
	}

	/**
	 * Massive shout-out to Cortex/Marshall for this bit of code
	 * used from HRKChat
	 */
	public static function resolveTags(string $line): array{
		$tags = [];

		if(preg_match_all(self::REGEX(), $line, $matches)){
			$tags = $matches[1];
		}

		return $tags;
	}

	/**
	 * Checks if the required virions/libraries are present before enabling the plugin.
	 */
	public static function validateVirions(LevwortScore $plugin): bool{
		$requiredVirions = [
			"ScoreFactory"   => ScoreFactory::class,
			"UpdateNotifier" => UpdateNotifier::class,
			"ConfigUpdater"  => ConfigUpdater::class
		];

		$return = true;

		foreach($requiredVirions as $name => $class){
			if(!class_exists($class)){
				$plugin->getLogger()->error("LevwortScore plugin will only work if you use the plugin phar from Poggit. [Missing: $name virion]");
				$plugin->getServer()->getPluginManager()->disablePlugin($plugin);
				$return = false;

				break;
			}
		}

		return $return;
	}

	public static function setTimezone(): bool{
		return date_default_timezone_set(LevwortScoreSettings::getTimezone());
	}
}
