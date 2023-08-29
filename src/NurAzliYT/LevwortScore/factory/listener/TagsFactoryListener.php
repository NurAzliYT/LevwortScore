<?php
declare(strict_types = 1);

namespace NurAzliYT\LevwortScore\factory\listener;

use NurAzliYT\LevwortScore\event\PlayerTagUpdateEvent;
use NurAzliYT\LevwortScore\event\ServerTagUpdateEvent;
use NurAzliYT\LevwortScore\scoreboard\ScoreTag;
use NurAzliYT\LevwortScore\LevwortScore;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExperienceChangeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use function count;

class FactoryListener implements Listener {

	public function __construct(
		private LevwortScore $plugin
	) {}

	public function onJoin(PlayerJoinEvent $event) {
		(new ServerTagUpdateEvent(new ScoreTag("levwortscore.online", (string) count($this->plugin->getServer()->getOnlinePlayers()))))->call();
        $worldPlayers = $event->getPlayer()->getWorld()->getPlayers();
        $worldCount = count($worldPlayers);
        foreach ($worldPlayers as $player) {
            (new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.world_player_count", (string) $worldCount)))->call();
        }
    }

	public function onQuit(PlayerQuitEvent $event) {
		$this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($event): void {
			(new ServerTagUpdateEvent(new ScoreTag("levwortscore.online", (string) count($this->plugin->getServer()->getOnlinePlayers()))))->call();
            $worldPlayers = $event->getPlayer()->getWorld()->getPlayers();
            $worldCount = count($worldPlayers);
            foreach ($worldPlayers as $player) {
                (new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.world_player_count", (string) $worldCount)))->call();
            }
		}), 20);
	}

	public function onDamage(EntityDamageEvent $event) {
		$player = $event->getEntity();
		if (!$player instanceof Player) return;
		(new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.health", (string) ((int) $player->getHealth()))))->call();
		(new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.max_health", (string) $player->getMaxHealth())))->call();
	}

	public function onRegainHealth(EntityRegainHealthEvent $event) {
		$player = $event->getEntity();
		if (!$player instanceof Player) return;
		(new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.health", (string) ((int) $player->getHealth()))))->call();
		(new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.max_health", (string) $player->getMaxHealth())))->call();
	}

	public function onExperienceChange(PlayerExperienceChangeEvent $event) {
		$player = $event->getEntity();
		if (!$player instanceof Player) return;
		(new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.xp_level", (string) ((int) $player->getXpManager()->getXpLevel()))))->call();
		(new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.xp_progress", (string) ((int) $player->getXpManager()->getXpProgress()))))->call();
		(new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.xp_remainder", (string) ((int) $player->getXpManager()->getRemainderXp()))))->call();
		(new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.xp_current_total", (string) ((int) $player->getXpManager()->getCurrentTotalXp()))))->call();
	}

	public function onMove(PlayerMoveEvent $event) {
		$fX = (int) $event->getFrom()->getX();
		$fY = (int) $event->getFrom()->getY();
		$fZ = (int) $event->getFrom()->getZ();
		$tX = (int) $event->getTo()->getX();
		$tY = (int) $event->getTo()->gety();
		$tZ = (int) $event->getTo()->getZ();

		if ($fX === $tX && $fY === $tY && $fZ === $tZ)  return;

		$player = $event->getPlayer();
		(new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.x", (string) ((int) $player->getPosition()->getX()))))->call();
		(new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.y", (string) ((int) $player->getPosition()->getY()))))->call();
		(new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.z", (string) ((int) $player->getPosition()->getZ()))))->call();
	}

	public function onTeleport(EntityTeleportEvent $event) {
		$player = $event->getEntity();
		$target = $event->getTo()->getWorld();
        $prevWorld = $event->getFrom()->getWorld();

		if (!$player instanceof Player) return;

        $worldPlayers = $target->getPlayers();
        $prevWorldPlayers = $prevWorld->getPlayers();
        if($target !== $event->getFrom()->getWorld()){
            $worldCount = count($worldPlayers) + 1;
            $worldPlayers[$player->getId()] = $player;
            $prevWorldCount = count($prevWorldPlayers) - 1;
            unset($prevWorldPlayers[$player->getId()]);
            foreach ($prevWorldPlayers as $prevWorldPlayer) {
                (new PlayerTagUpdateEvent($prevWorldPlayer, new ScoreTag("levwortscore.world_player_count", (string) $prevWorldCount)))->call();
            }
        }else{
            $worldCount = count($worldPlayers);
        }
        foreach ($worldPlayers as $worldPlayer) {
            (new PlayerTagUpdateEvent($worldPlayer, new ScoreTag("levwortscore.world_player_count", (string) $worldCount)))->call();
        }

        (new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.level_name", $target->getDisplayName())))->call();
		(new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.world_name", $target->getDisplayName())))->call();

		(new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.level_folder_name", $target->getFolderName())))->call();
		(new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.world_folder_name", $target->getFolderName())))->call();
	}

	public function onItemHeld(PlayerItemHeldEvent $event) {
		$player = $event->getPlayer();
		$item = $event->getItem();
		(new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.item_name", $item->getName())))->call();
		(new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.item_id", (string) $item->getId())))->call();
		(new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.item_meta", (string) $item->getMeta())))->call();
		(new PlayerTagUpdateEvent($player, new ScoreTag("levwortscore.item_count", (string) $item->getCount())))->call();
	}
}
