<?php
declare(strict_types = 1);

namespace NurAzliYT/LevwortScore\event;

use NurAzliYT\LevwortScore\scoreboard\ScoreTag;
use pocketmine\player\Player;

/**
 * Same as PlayerTagUpdateEvent but provides an easier way
 * to send updates for multiple tags at the same time.
 *
 * @see PlayerTagUpdateEvent
 */
class PlayerTagsUpdateEvent extends PlayerEvent{

	/** @var ScoreTag[] */
	private array $tags = [];

	/**
	 * @param ScoreTag[] $tags
	 */
	public function __construct(Player $player, array $tags){
		$this->tags = $tags;

		parent::__construct($player);
	}

	/**
	 * @param ScoreTag[] $tags
	 */
	public function setTags(array $tags): void{
		$this->tags = $tags;
	}

	/**
	 * @return ScoreTag[]
	 */
	public function getTags(): array{
		return $this->tags;
	}

	public function addTag(ScoreTag $tag): void{
		$this->tags[] = $tag;
	}
}
