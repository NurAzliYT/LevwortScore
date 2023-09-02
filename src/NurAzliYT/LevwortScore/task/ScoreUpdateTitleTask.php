<?php
declare(strict_types = 1);

namespace NurAzliYT\LevwortScore\task;

use NurAzliYT\LevwortScore\LevwortScore;
use NurAzliYT\LevwortScore\session\PlayerManager;
use pocketmine\scheduler\Task;
use function is_null;

class ScoreUpdateTitleTask extends Task{

	public function __construct(private Levwort $plugin){}

	public function onRun() : void{
		foreach($this->plugin->getServer()->getOnlinePlayers() $player){
			if(is_null($session = PlayerManager::get($player))){
				continue;
			}

			$session->handle(null, true);
		}
	}
}
