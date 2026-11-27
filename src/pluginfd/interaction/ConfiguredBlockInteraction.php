<?php

declare(strict_types=1);

namespace pluginfd\interaction;

use pluginfd\block\ConfiguredBlock;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\player\Player;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;

final class ConfiguredBlockInteraction{

    public function handle(PlayerInteractEvent $event): void{
        $block=$event->getBlock();
        $player=$event->getPlayer();
        $item=$event->getItem();

        if(!$block instanceof ConfiguredBlock){
            return;
        }

        if(!$block->hasBones()){
            return;
        }

        $action=$event->getAction();

        // ЛКМ: удаляем кость.
        if($action===PlayerInteractEvent::LEFT_CLICK_BLOCK){
            $event->cancel();

            $bit=$this->getBoneBit($event,$player);

            if($bit===null || !$block->canRemoveBone($bit)){
                return;
            }

            $mask=$block->getMask();
            $newMask=$mask&~(1<<$bit);

            if($newMask===0){
                $player->getWorld()->setBlock(
                    $block->getPosition(),
                    VanillaBlocks::AIR()
                );

                $player->sendMessage("§cVoxel block removed");
                return;
            }

            $newBlock=clone $block;
            $newBlock->setMask($newMask);

            $player->getWorld()->setBlock(
                $block->getPosition(),
                $newBlock
            );

            $player->sendMessage("§cRemoved bone: §f".$bit);
            return;
        }

        if($action!==PlayerInteractEvent::RIGHT_CLICK_BLOCK){
            return;
        }

        $materialItem=match($block->getMaterialKey()){
            "sand","sand_white","sand_beige","sand_orange"=>VanillaBlocks::SAND()->asItem(),
            "dirt"=>VanillaBlocks::DIRT()->asItem(),
            "stone"=>VanillaBlocks::STONE()->asItem(),
            default=>null
        };

        if($materialItem===null){
            return;
        }

        if(!$item->equals($materialItem)){
            return;
        }

        $bit=$this->getBoneBit($event,$player);

        if($bit===null){
            return;
        }

        $mask=$block->getMask();

        if(($mask&(1<<$bit))!==0){
            return;
        }

        $event->cancel();

        $newBlock=clone $block;
        $newBlock->setMask($mask|(1<<$bit));

        $player->getWorld()->setBlock(
            $block->getPosition(),
            $newBlock
        );

        $player->sendMessage("§aAdded bone: §f".$bit);
    }

	private function getBoneBit(
		PlayerInteractEvent $event,
		Player $player
	): ?int{
		$block=$event->getBlock();
		$position=$block->getPosition();
		$face=$event->getFace();

		// Для слоистых блоков сверху и снизу выбираем
		// ближайший существующий крайний слой.
		if(
			$event->getAction()===PlayerInteractEvent::LEFT_CLICK_BLOCK &&
			in_array($block->getVoxelType(),["layered","layered_half"],true)
		){
			$mask=$block->getMask();
			$count=$block->getBoneCount();

			if($face===Facing::UP){
				for($bit=$count-1;$bit>=0;--$bit){
					if(($mask&(1<<$bit))!==0){
						return $bit;
					}
				}

				return null;
			}

			if($face===Facing::DOWN){
				for($bit=0;$bit<$count;++$bit){
					if(($mask&(1<<$bit))!==0){
						return $bit;
					}
				}

				return null;
			}
		}

		if($event->getAction()===PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			$touch=$event->getTouchVector();

			$hit=new Vector3(
				$position->x+$touch->x,
				$position->y+$touch->y,
				$position->z+$touch->z
			);
		}else{
			$hit=$this->getRayHit(
				$player,
				$position,
				$face
			);

			if($hit===null){
				return null;
			}
		}

		return $block->getBoneBitAt(
			$hit->x-$position->x,
			$hit->y-$position->y,
			$hit->z-$position->z
		);
	}

    private function getRayHit(
        Player $player,
        Vector3 $position,
        int $face
    ): ?Vector3{
        $origin=$player->getEyePos();
        $direction=$player->getDirectionVector();

        $plane=match($face){
            Facing::DOWN=>["y",$position->y],
            Facing::UP=>["y",$position->y+1],
            Facing::NORTH=>["z",$position->z],
            Facing::SOUTH=>["z",$position->z+1],
            Facing::WEST=>["x",$position->x],
            Facing::EAST=>["x",$position->x+1],
            default=>null
        };

        if($plane===null){
            return null;
        }

        [$axis,$coordinate]=$plane;
        $component=$direction->{$axis};

        if(abs($component)<0.000001){
            return null;
        }

        $distance=($coordinate-$origin->{$axis})/$component;

        if($distance<0){
            return null;
        }

        $hit=$origin->add(
            $direction->x*$distance,
            $direction->y*$distance,
            $direction->z*$distance
        );

        if(
            $hit->x<$position->x-0.0001 ||
            $hit->x>$position->x+1.0001 ||
            $hit->y<$position->y-0.0001 ||
            $hit->y>$position->y+1.0001 ||
            $hit->z<$position->z-0.0001 ||
            $hit->z>$position->z+1.0001
        ){
            return null;
        }

        return $hit;
    }
}