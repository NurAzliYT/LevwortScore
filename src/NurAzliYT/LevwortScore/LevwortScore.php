<?php
declare(strict_types = 1);

namespace NurAzliYT\LevwortScore;

use NurAzliYT\LevwortScore\commands\LevwortScoreCommand;
use NurAzliYT\LevwortScore\factory\TagsFactory;
use NurAzliYT\LevwortScore\session\PlayerManager;
use NurAzliYT\LevwortScore\session\PlayerSessionHandler;
use NurAzliYT\LevwortScore\task\ScoreUpdateTitleTask;
use NurAzliYT\LevwortScore\utils\HelperUtils;
use NurAzliYT\LevwortScore\utils\TitleUtils;
use JackMD\ConfigUpdater\ConfigUpdater;
use NurAzliYT\LevwortScore\utils\Utils;
use jackmd\scorefactory\ScoreFactory;
use JackMD\UpdateNotifier\UpdateNotifier;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use function is_array;

class LevwortScore extends PluginBase{
	use SingletonTrait;

	private const CONFIG_VERSION = 11;
	private const LEVWORTSCORE_VERSION = 3;

	private ?Config $scoreConfig;

	/**
	 * @return LevwortScore|null
	 */
	public static function getInstance(): ?LevwortScore{
		return self::$instance;
	}

	public function onLoad(): void{
		self::setInstance($this);
	}

	public function onEnable(): void{
		$this->loadConfigs();

		if(!Utils::validateVirions($this)){
			return;
		}

		UpdateNotifier::checkUpdate($this->getDescription()->getName(), $this->getDescription()->getVersion());
		LevwortScoreSettings::init($this);

		$this->validateConfigs();

		if(!$this->canLoad()){
			return;
		}

		if(LevwortScoreSettings::isTimezoneChanged()){
			if(Utils::setTimezone()){
				$this->getLogger()->notice("Server timezone successfully set to " . LevwortScoreSettings::getTimezone());
			}else{
				$this->getLogger()->error("Unable to set timezone. Invalid timezone: " . LevwortScoreSettings::getTimezone() . ", provided under 'time.zone' in config.yml.");
			}
		}

		if(LevwortScoreSettings::areFlickeringTitlesEnabled()){
			$this->getScheduler()->scheduleRepeatingTask(new ScoreUpdateTitleTask($this), LevwortScoreSettings::getFlickerRate());
		}

		$this->getServer()->getPluginManager()->registerEvents(new PlayerSessionHandler(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

		$this->getServer()->getCommandMap()->register("levwortscore", new LevwortScoreCommand($this));

		if (LevwortScoreSettings::isTagFactoryEnabled()) TagsFactory::init($this);
	}

	public function onDisable(): void{
		$this->scoreConfig = null;

		LevwortScoreSettings::destroy();
		PlayerManager::destroyAll();

		self::$instance = null;
	}

	private function loadConfigs(): void{
		$this->saveDefaultConfig();

		$this->saveResource("levanda.yml");
		$this->scoreConfig = new Config($this->getDataFolder() . "LevwortScore.yml", Config::YAML);
	}

	private function validateConfigs(): void{
		$updated = false;

		if(ConfigUpdater::checkUpdate($this, $this->getConfig(), "config-version", self::CONFIG_VERSION)){
			$updated = true;
			$this->reloadConfig();
		}

		if(ConfigUpdater::checkUpdate($this, $this->scoreConfig, "LevwortScore-version", self::LEVWORTSCORE_VERSION)){
			$updated = true;
			$this->scoreConfig = new Config($this->getDataFolder() . "LevwortScore.yml", Config::YAML);
		}

		if($updated){
			LevwortScoreSettings::destroy();
			LevwortScoreSettings::init($this);
		}
	}

	private function canLoad(): bool{
		$load = true;
		$errors = [];

		if(!LevwortScoreSettings::isMultiWorld() && empty(LevwortScoreSettings::getDefaultBoard())){
			$load = false;
			$errors[] = "Please set the lines under 'default-board' properly, in LevwortScore.yml.";
		}

		if(LevwortScoreSettings::useDefaultBoard() && empty(LevwortScoreSettings::getDefaultBoard())){
			$load = false;
			$errors[] = "Please set the lines under 'default-board' properly, in LevwortScore.yml.";
		}

		if(LevwortScoreSettings::areFlickeringTitlesEnabled() && empty(LevwortScoreSettings::getTitles())){
			$load = false;
			$errors[] = "Please set the lines under 'titles.lines' properly, in LevwortScore.yml.";
		}

		if(!is_array($this->getConfig()->get("disabled-worlds", []))){
			$load = false;
			$errors[] = "The 'disabled-worlds' key in config.yml must be of the type array. Please set it properly.";
		}

		if(!$load){
			foreach($errors as $error){
				$this->getLogger()->error($error);
			}

			$this->getServer()->getPluginManager()->disablePlugin($this);
		}

		return $load;
	}

	public function getScoreConfig(): Config{
		return $this->scoreConfig;
	}

	public function setScore(Player $player, bool $calledFromTask): void{
		if(!$player->isOnline()){
			return;
		}

		if(HelperUtils::isDisabled($player) || LevwortScoreSettings::isInDisabledWorld($player->getWorld()->getFolderName())){
			ScoreFactory::removeObjective($player);

			return;
		}

		ScoreFactory::setObjective($player, TitleUtils::getTitle($calledFromTask));
		ScoreFactory::sendObjective($player);
	}
}
