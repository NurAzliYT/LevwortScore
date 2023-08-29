<?php
declare(strict_types = 1);

namespace NurAzliYT\LevwortScore\event;

use NurAzliYT\LevwortScore\LevwortScore;
use pocketmine\event\Event;

abstract class LevwortScoreEvent extends Event{

	/** @var LevwortScore|null */
	protected ?LevwortScore $plugin = null;

	public function __construct(){
		$this->plugin = LevwortScore::getInstance();
	}

	public function getPlugin(): ?LevwortScore{
		return $this->plugin;
	}
}
