<?php
declare(strict_types = 1);

namespace NurAzliYT\LevwortScore\factory;

use NurAzliYT\LevwortScore\event\PlayerTagUpdateEvent;
use NurAzliYT\LevwortScore\event\ServerTagsUpdateEvent;
use NurAzliYT\LevwortScore\factory\listener\FactoryListener;
use NurAzliYT\LevwortScore\factory\listener\TagResolveListener;
use NurAzliYT\LevwortScore\scoreboard\ScoreTag;
use NurAzliYT\LevwortScore\LevwortScore;
use NurAzliYT\LevwortScore\LevwortScoreSettings;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Process;

class TagsFactory {

	public static function init(LevwortScore $plugin) {
		$server = $plugin->getServer();

		$server->getPluginManager()->registerEvents(new FactoryListener($plugin), $plugin);
		$server->getPluginManager()->registerEvents(new TagResolveListener($plugin), $plugin);

		$task = new ClosureTask(function() use ($plugin): void{
			$server = $plugin->getServer();

			foreach($server->getOnlinePlayers() as $player){
				(new PlayerTagUpdateEvent($player, new ScoreTag("levowrtscore.ping", (string) ($player->getNetworkSession()->getPing()))))->call();
			}

			(new ServerTagsUpdateEvent([
				new ScoreTag("levwortscore.load", (string) $server->getTickUsage()),
				new ScoreTag("levwortscore.tps", (string) $server->getTicksPerSecond()),
				new ScoreTag("levwortscore.time", date(LevwortScoreSettings::getTimeFormat())),
				new ScoreTag("levwortscore.date", date(LevwortScoreSettings::getDateFormat()))
			]))->call();

			if(LevwortScoreSettings::areMemoryTagsEnabled()){
				$rUsage = Process::getRealMemoryUsage();
				$mUsage = Process::getAdvancedMemoryUsage();

				$globalMemory = "MAX";
				if($server->getConfigGroup()->getProperty("memory.global-limit") > 0){
					$globalMemory = number_format(round($server->getConfigGroup()->getProperty("memory.global-limit"), 2), 2) . " MB";
				}

				(new ServerTagsUpdateEvent([
					new ScoreTag("levwortscore.memory_main_thread", number_format(round(($mUsage[0] / 1024) / 1024, 2), 2) . " MB"),
					new ScoreTag("levwortscore.memory_total", number_format(round(($mUsage[1] / 1024) / 1024, 2), 2) . " MB"),
					new ScoreTag("levwortscore.memory_virtual", number_format(round(($mUsage[2] / 1024) / 1024, 2), 2) . " MB"),
					new ScoreTag("levwortscore.memory_heap", number_format(round(($rUsage[0] / 1024) / 1024, 2), 2) . " MB"),
					new ScoreTag("levwortscore.memory_global", $globalMemory)
				]))->call();
			}
		});

		$plugin->getScheduler()->scheduleRepeatingTask($task, ScoreHudSettings::getTagFactoryUpdatePeriod() * 20);
	}
}
