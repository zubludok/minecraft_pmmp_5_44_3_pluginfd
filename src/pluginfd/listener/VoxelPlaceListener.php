<?php

declare(strict_types=1);

namespace pluginfd\listener;

use customiesdevs\customies\block\CustomiesBlockFactory;
use pluginfd\item\VoxelPlaceItem;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\block\Air;

final class VoxelPlaceListener implements Listener{

    public function onInteract(PlayerInteractEvent $event): void{
        if($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK){
            return;
        }

        $item = $event->getItem();

        if(!$item instanceof VoxelPlaceItem){
            return;
        }

        $event->cancel();

        $player = $event->getPlayer();
        $heldItem = $player->getInventory()->getItemInHand();

        // Проверяем, что в руке всё ещё нужный предмет
        if(
            !$heldItem instanceof VoxelPlaceItem ||
            $heldItem->getBlockIdentifier() !== $item->getBlockIdentifier()
        ){
            return;
        }

        $clickedBlock = $event->getBlock();

        // Получаем блок рядом с тем, по которому нажали
        $targetBlock = $clickedBlock->getSide($event->getFace());

        // Не устанавливаем блок в занятое пространство
		if(!$targetBlock instanceof Air){
			return;
		}

        // Получаем зарегистрированный блок
        $block = CustomiesBlockFactory::getInstance()->get(
            $item->getBlockIdentifier()
        );

        // Устанавливаем его в мир
        $player->getWorld()->setBlock(
            $targetBlock->getPosition(),
            $block
        );

        // В творческом режиме предмет не расходуется
        if(!$player->isCreative()){
            if($heldItem->getCount() <= 1){
                $player->getInventory()->setItemInHand(VanillaBlocks::AIR()->asItem());
            }else{
                $heldItem->setCount($heldItem->getCount() - 1);
                $player->getInventory()->setItemInHand($heldItem);
            }
        }
    }
}