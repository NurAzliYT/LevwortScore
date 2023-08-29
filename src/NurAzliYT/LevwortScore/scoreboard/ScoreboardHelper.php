<?php
declare(strict_types = 1);

namespace NurAzliYT\LevwortScore\scoreboard;

use NurAzliYT\LevwortScore\event\TagsResolveEvent;
use NurAzliYT\LevwortScore\ScoreSettings;
use NurAzliYT\LevwortScore\session\PlayerSession;
use NurAzliYT\LevwortScore\utils\Utils;
use function array_merge;

class ScoreboardHelper{

	private static function constructTag(PlayerSession $session, string $tagName): ScoreTag{
		$tag = new ScoreTag($tagName, "");

		$ev = new TagsResolveEvent($session->getPlayer(), $tag);
		$ev->call();

		return $ev->getTag();
	}

	public static function createDefault(PlayerSession $session): Scoreboard{
		$tags = [];

		foreach(self::resolveLines($lines = LevwortScoreSettings::getDefaultBoard()) as $tagName){
			$tags[] = self::constructTag($session, $tagName);
		}

		return new Scoreboard($session, $lines, $tags);
	}

	public static function create(PlayerSession $session, string $world): Scoreboard{
		$tags = [];

		foreach(self::resolveLines($lines = LevwortScoreSettings::getScoreboard($world)) as $tagName){
			$tags[] = self::constructTag($session, $tagName);
		}

		return new Scoreboard($session, $lines, $tags);
	}

	/**
	 * Separates the tags from the lines and returns all the tag names
	 */
	public static function resolveLines(array $lines): array{
		$tags = [];

		foreach($lines as $line){
			$tags = array_merge($tags, Utils::resolveTags($line));
		}

		return $tags;
	}
}
