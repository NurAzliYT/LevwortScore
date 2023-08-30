<?php
declare(strict_types = 1);

namespace NurAzliYT\LevwortScore\utils;

use NurAzliYT\LevwortScore\LevwortScoreSettings;

class TitleUtils{

	private static $titleIndex = 0;

	public static function getTitle(bool $calledFromTask = false): string{
		$title = LevwortScoreSettings::getTitle();

		if(ScoreHudSettings::areFlickeringTitlesEnabled()){
			$titles = LevwortScoreSettings::getTitles();

			if(!isset($titles[self::$titleIndex])){
				self::$titleIndex = 0;
			}

			$title = $titles[self::$titleIndex];

			if($calledFromTask){
				self::$titleIndex++;
			}
		}

		return $title;
	}
}
