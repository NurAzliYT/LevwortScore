<?php
declare(strict_types = 1);

namespace NurAzliYT\LevwortScore\utils;

use pocketmine\player\Player;

class HelperUtils{

	private static $players = [];

	public static function disable(Player $player): void{
		self::$players[$player->getUniqueId()->toString()] = $player;
	}

	public static function destroy(Player $player): void{
		unset(self::$players[$player->getUniqueId()->toString()]);
	}

	public static function isDisabled(Player $player): bool{
		return isset(self::$players[$player->getUniqueId()->toString()]);
	}
}
