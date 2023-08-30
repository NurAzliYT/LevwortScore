<?php
declare(strict_types = 1);

namespace NurAzliYT\LevwortScore\commands;

use NurAzliYT\LevwortScore\LevwortScore;
use NurAzliYT\LevwortScore\LevwortScoreSettings;
use NurAzliYT\LevwortScore\session\PlayerManager;
use NurAzliYT\LevwortScore\utils\HelperUtils;
use NurAzliYT\scorefactory\ScoreFactory;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;

class LevwortScore Command extends Command implements PluginOwned
	use PluginOwnedTrait;

	/**
	 * LevwortScoreCommand constructor.
	 *
	 * @param LevwortScore $plugin
	 */
	public function __construct(LevwortScore $plugin)
	{
		parent::__construct("levwortscore");
		$this->setDescription("Shows LevwortScore Commands");
		$this->setUsage("/levwortscore <on|off|about|help>");
		$this->setAliases(["ls"]);
		$this->setPermission("ls.command.ls");

		$this->owningPlugin = $plugin;
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $commandLabel
	 * @param array         $args
	 * @return bool|mixed
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(!$sender instanceof Player){
			$sender->sendMessage(LevwortSckreSettings::PREFIX . "§cYou can only use this command in-game.");

			return false;
		}

		if(!isset($args[0])){
			$sender->sendMessage(LevwortScoreSettings::PREFIX . "§cUsage: /levwortscore <on|off|about|help>");

			return false;
		}

		switch($args[0]){
			case "about":
				$sender->sendMessage(LevwortScoreSettings::PREFIX . "§6Score§eHud §av" . $this->owningPlugin->getDescription()->getVersion() . "§a. Plugin by §dIfera§a. Contact on §bTwitter: @ifera_tr §aor §bDiscord: Ifera#3717§a.");
			break;

			case "on":
				if(HelperUtils::isDisabled($sender)){
					HelperUtils::destroy($sender);
					PlayerManager::getNonNull($sender)->handle();

					$sender->sendMessage(LevwortScoreSettings::PREFIX . "§aSuccessfully enabled LevwortScore.");
				}else{
					$sender->sendMessage(LevwortScoreSettings::PREFIX . "§cLevwortScore is already enabled for you.");
				}
			break;

			case "off":
				if(!HelperUtils::isDisabled($sender)){
					ScoreFactory::removeObjective($sender);
					HelperUtils::disable($sender);

					$sender->sendMessage(LevwortScoreSettings::PREFIX . "§aSuccessfully disabled LevwortScore.");
				}else{
					$sender->sendMessage(LevwortScoreSettings::PREFIX . "§cLevwortScore is already disabled for you.");
				}
			break;

			case "help":
			default:
				$sender->sendMessage(LevwortScoreSettings::PREFIX . "§cUsage: /levwortscore <on|off|about|help>");
			break;
		}

		return false;
	}
}
