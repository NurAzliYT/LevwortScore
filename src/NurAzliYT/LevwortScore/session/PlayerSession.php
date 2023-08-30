<?php
declare(strict_types = 1);

namespace NurAzliYT\LevwortScore\session;

use NurAzliYT\LevwortScore\scoreboard\Scoreboard;
use NurAzliYT\LevwortScore\scoreboard\ScoreboardHelper;
use NurAzliYT\LevwortScore\LevwortScore;
use NurAzliYT\LevwortScore\LevwortScoreSettings;
use NurAzliYT\\utils\HelperUtils;
use Ifera\scorefactory\ScoreFactory;
use pocketmine\player\Player;
use function is_null;

class PlayerSession{

	/** @var LevwortScore */
	private LevwortScore $plugin;
	/** @var Scoreboard|null */
	private ?Scoreboard $scoreboard;

	public function __construct(private Player $player){
		$this->plugin = LevwortScore::getInstance();
		$this->scoreboard = null;
	}

	public function getPlayer(): Player{
		return $this->player;
	}

	public function getScoreboard(): ?Scoreboard{
		return $this->scoreboard;
	}

	public function setScoreboard(Scoreboard $scoreboard): void{
		$this->scoreboard = $scoreboard;
	}

	public function handle(string $world = null, bool $calledFromTask = false): void{
		$player = $this->player;

		if(!$player->isOnline() || HelperUtils::isDisabled($player)){
			return;
		}

		$world = $world ?? $player->getWorld()->getFolderName();

		// remove scoreboard if player is in a world where scoreboard is disabled
		if(LevwortScoreSettings::isInDisabledWorld($world)){
			ScoreFactory::removeObjective($player);

			return;
		}

		// check for multi world board first
		if(LevwortScoreSettings::isMultiWorld()){
			// construct the board for this level and send
			if(LevwortScoreSettings::worldExists($world)){
				$this->plugin->setScore($player, $calledFromTask);

				$scoreboard = ScoreboardHelper::create($this, $world);
				$scoreboard->update()->display();

				$this->scoreboard = $scoreboard;

				return;
			}

			// use the default board since the scoreboard for the world is unknown
			if(LevwortScoreSettings::useDefaultBoard()){
				$this->constructDefaultBoard($calledFromTask);

				return;
			}

			// no scoreboard is to be displayed
			ScoreFactory::removeObjective($player);

			return;
		}

		// construct the default board since multi world support is not enabled
		$this->constructDefaultBoard($calledFromTask);
	}

	/**
	 * Used for handling default scoreboard
	 */
	private function constructDefaultBoard(bool $calledFromTask): void{
		$this->plugin->setScore($this->player, $calledFromTask);

		if($calledFromTask && !is_null($this->scoreboard)){
			$this->scoreboard->display();

			return;
		}

		$scoreboard = ScoreboardHelper::createDefault($this);
		$scoreboard->update()->display();

		$this->scoreboard = $scoreboard;
	}

	public function close(): void{
		HelperUtils::destroy($this->player);
		ScoreFactory::removeObjective($this->player, true);
	}
}
