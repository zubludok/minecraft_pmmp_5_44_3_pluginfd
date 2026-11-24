<?php

declare(strict_types=1);

namespace pluginfd\listener;

use pluginfd\block\ConfiguredBlock;
use pluginfd\block\ConfiguredResourceBlock;
use pluginfd\interaction\ConfiguredBlockInteraction;
use pluginfd\interaction\ConfiguredResourceBlockInteraction;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\plugin\PluginBase;
use pluginfd\block\ConfiguredTreeBlock;
use pluginfd\interaction\ConfiguredTreeBlockInteraction;

final class DebugListenerBlock implements Listener{

    private ConfiguredBlockInteraction $blockInteraction;
    private ConfiguredResourceBlockInteraction $resourceInteraction;
	private ConfiguredTreeBlockInteraction $treeInteraction;
	
	public function __construct(PluginBase $plugin){
		$this->blockInteraction = new ConfiguredBlockInteraction();
		$this->resourceInteraction = new ConfiguredResourceBlockInteraction();
		$this->treeInteraction = new ConfiguredTreeBlockInteraction();
	}

	public function onBlockInteract(PlayerInteractEvent $event) : void{
		$block = $event->getBlock();

		if($block instanceof ConfiguredTreeBlock){
			$this->treeInteraction->handle($event);
			return;
		}

		if($block instanceof ConfiguredResourceBlock){
			$this->resourceInteraction->handle($event);
			return;
		}

		if($block instanceof ConfiguredBlock){
			$this->blockInteraction->handle($event);
		}
	}

	public function onBlockBreak(BlockBreakEvent $event) : void{
		$block = $event->getBlock();

		if(
			$block instanceof ConfiguredBlock ||
			$block instanceof ConfiguredResourceBlock
		){
			$event->cancel();
		}
	}
}