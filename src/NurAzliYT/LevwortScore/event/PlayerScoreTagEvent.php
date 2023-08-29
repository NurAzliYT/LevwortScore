<?php
declare(strict_types = 1);

namespace NurAzliYT\LevwortScore\event;

use NurAzliYT\LevwortScore\scoreboard\ScoreTag;
use pocketmine\player\Player;

abstract class PlayerScoreTagEvent extends PlayerEvent{

	/** @var ScoreTag */
	protected ScoreTag $tag;

	public function __construct(Player $player, ScoreTag $tag){
		$this->tag = $tag;

		parent::__construct($player);
	}

	public function getTag(): ScoreTag{
		return $this->tag;
	}

	public function setTag(ScoreTag $tag): void{
		$this->tag = $tag;
	}
}
