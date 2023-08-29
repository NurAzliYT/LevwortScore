<?php
declare(strict_types = 1);

namespace NurAzliYT\LevwortScore\session;

use pocketmine\player\Player;

class PlayerManager{

	/** @var PlayerSession[] */
	private static $sessions = [];

	public static function create(Player $player): void{
		self::$sessions[$player->getUniqueId()->toString()] = $session = new PlayerSession($player);
		$session->handle();
	}

	public static function destroy(Player $player): void{
		if(!$player->isOnline()){
			return;
		}

		if(!isset(self::$sessions[$uuid = $player->getUniqueId()->toString()])){
			return;
		}

		self::$sessions[$uuid]->close();
		unset(self::$sessions[$uuid]);
	}

	public static function get(Player $player): ?PlayerSession{
		return self::$sessions[$player->getUniqueId()->toString()] ?? null;
	}

	public static function getNonNull(Player $player): PlayerSession{
		return self::$sessions[$player->getUniqueId()->toString()];
	}

	/**
	 * @return PlayerSession[]
	 */
	public static function getAll(): array{
		return self::$sessions;
	}

	public static function destroyAll(): void{
		foreach(self::$sessions as $session){
			self::destroy($session->getPlayer());
		}
	}
}
