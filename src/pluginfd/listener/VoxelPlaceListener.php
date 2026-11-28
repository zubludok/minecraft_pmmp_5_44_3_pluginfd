<?php

declare(strict_types=1);

namespace pluginfd\listener;

use customiesdevs\customies\block\CustomiesBlockFactory;

use pluginfd\block\ConfiguredBlock;
use pluginfd\block\VoxelPlaceholderBlock;
use pluginfd\item\VoxelPlaceItem;

use pocketmine\block\Air;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Facing;

final class VoxelPlaceListener implements Listener{

    public function onInteract(PlayerInteractEvent $event): void{
        if($event->getAction()!==PlayerInteractEvent::RIGHT_CLICK_BLOCK){
            return;
        }

        $item=$event->getItem();
        $targetIdentifier=null;

        if($item instanceof VoxelPlaceItem){
            $targetIdentifier=$item->getBlockIdentifier();
        }else{
            $targetIdentifier=VoxelPlaceholderBlock::getTargetIdentifier(
                $item->getTypeId()
            );
        }

        if($targetIdentifier===null){
            return;
        }

        $event->cancel();

        $player=$event->getPlayer();
        $heldItem=$player->getInventory()->getItemInHand();

        if($heldItem->getTypeId()!==$item->getTypeId()){
            return;
        }

        $clickedBlock=$event->getBlock();
        $targetBlock=$clickedBlock->getSide($event->getFace());

        if(!$targetBlock instanceof Air){
            return;
        }

        $block=CustomiesBlockFactory::getInstance()->get($targetIdentifier);

        // Определяем начальную ориентацию cuber.
        if($block instanceof ConfiguredBlock && $block->getVoxelType()==="cuber"){
            $touch=$event->getTouchVector();
            $upper=$touch->y>=0.5;

            $side=match($event->getFace()){
                Facing::NORTH=>0,
                Facing::EAST=>1,
                Facing::SOUTH=>2,
                Facing::WEST=>3,
                default=>match($player->getHorizontalFacing()){
                    Facing::NORTH=>0,
                    Facing::EAST=>1,
                    Facing::SOUTH=>2,
                    Facing::WEST=>3,
                    default=>0
                }
            };

            $block=clone $block;
            $block->setMask(1<<(($upper?4:0)+$side));
        }

        $player->getWorld()->setBlock(
            $targetBlock->getPosition(),
            $block
        );

        if(!$player->isCreative()){
            if($heldItem->getCount()<=1){
                $player->getInventory()->setItemInHand(VanillaBlocks::AIR()->asItem());
            }else{
                $heldItem->setCount($heldItem->getCount()-1);
                $player->getInventory()->setItemInHand($heldItem);
            }
        }
    }
}