<?php

declare(strict_types=1);

namespace pluginfd\interaction;

use pluginfd\block\ConfiguredBlock;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;

final class ConfiguredBlockInteraction{

    public function handle(PlayerInteractEvent $event) : void{
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $item = $event->getItem();

        if(!$block instanceof ConfiguredBlock){
            return;
        }

        $action = $event->getAction();

        // ЛКМ: удаляем кость.
        if($action === PlayerInteractEvent::LEFT_CLICK_BLOCK){
            $event->cancel();

            $bit = $this->getBoneBitForRemoval($event, $player);

            if($bit === null){
                return;
            }

            $mask = $block->getMask();

            if(($mask & (1 << $bit)) === 0){
                return;
            }

            $newMask = $mask & ~(1 << $bit);

            if($newMask === 0){
                $player->getWorld()->setBlock(
                    $block->getPosition(),
                    VanillaBlocks::AIR()
                );

                $player->sendMessage("§cVoxel block removed");
                return;
            }

            $newBlock = clone $block;
            $newBlock->setMask($newMask);

            $player->getWorld()->setBlock(
                $block->getPosition(),
                $newBlock
            );

            $player->sendMessage("§cRemoved bone: §f" . $bit);

            $checkBlock = $player->getWorld()->getBlock(
                $block->getPosition()
            );

            $player->sendMessage(
                "Mask: " . $checkBlock->getMask() .
                " | State ID: " . $checkBlock->getStateId() .
                " | Collision boxes: " . count($checkBlock->getCollisionBoxes())
            );

            return;
        }

        // ПКМ: добавляем кость.
        if($action !== PlayerInteractEvent::RIGHT_CLICK_BLOCK){
            return;
        }

        $material = strtolower($block->getName());

        $materialItem = match($material){
            "sand cubev" => VanillaBlocks::SAND()->asItem(),
            "dirt cubev" => VanillaBlocks::DIRT()->asItem(),
            "stone cubev" => VanillaBlocks::STONE()->asItem(),
            default => null
        };

        $player->sendMessage("§eBlock name: §f" . $block->getName());
        $player->sendMessage("§eHeld item: §f" . $item->getName());
        $player->sendMessage("§eHeld ID: §f" . $item->getTypeId());

        if($materialItem === null){
            $player->sendMessage("§cUnknown material!");
            return;
        }

        $player->sendMessage("§eExpected item: §f" . $materialItem->getName());
        $player->sendMessage("§eExpected ID: §f" . $materialItem->getTypeId());

        if(!$item->equals($materialItem)){
            $player->sendMessage("§cItem mismatch!");
            return;
        }

        $player->sendMessage("§aItem matched!");

        $bit = $this->getBoneBit($event, $player);

        if($bit === null){
            return;
        }

        $mask = $block->getMask();

        // Если кость уже существует, оставляем стандартное поведение.
        if(($mask & (1 << $bit)) !== 0){
            return;
        }

        $event->cancel();

        $newBlock = clone $block;
        $newBlock->setMask($mask | (1 << $bit));

        $player->getWorld()->setBlock(
            $block->getPosition(),
            $newBlock
        );

        $player->sendMessage("§aAdded bone: §f" . $bit);
    }

    private function getBoneBit(
        PlayerInteractEvent $event,
        Player $player
    ) : ?int{
        $block = $event->getBlock();
        $position = $block->getPosition();

        if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
            $touch = $event->getTouchVector();

            $hit = new Vector3(
                $position->x + $touch->x,
                $position->y + $touch->y,
                $position->z + $touch->z
            );
        }else{
            $hit = $this->getRayHit(
                $player,
                $position,
                $event->getFace()
            );

            if($hit === null){
                return null;
            }
        }

        $localX = $hit->x - $position->x;
        $localY = $hit->y - $position->y;
        $localZ = $hit->z - $position->z;

        $upper = $localY >= 0.5;
        $east = $localX >= 0.5;
        $south = $localZ >= 0.5;

        $corner = match(true){
            !$south && $east => 0,
            $south && $east => 1,
            !$south && !$east => 2,
            $south && !$east => 3
        };

        return $upper ? $corner : $corner + 4;
    }

    private function getRayHit(
        Player $player,
        Vector3 $position,
        int $face
    ) : ?Vector3{
        $origin = $player->getEyePos();
        $direction = $player->getDirectionVector();

        $plane = match($face){
            Facing::DOWN => ["y", $position->y],
            Facing::UP => ["y", $position->y + 1],
            Facing::NORTH => ["z", $position->z],
            Facing::SOUTH => ["z", $position->z + 1],
            Facing::WEST => ["x", $position->x],
            Facing::EAST => ["x", $position->x + 1],
            default => null
        };

        if($plane === null){
            return null;
        }

        [$axis, $coordinate] = $plane;
        $component = $direction->{$axis};

        if(abs($component) < 0.000001){
            return null;
        }

        $distance = ($coordinate - $origin->{$axis}) / $component;

        if($distance < 0){
            return null;
        }

        $hit = $origin->add(
            $direction->x * $distance,
            $direction->y * $distance,
            $direction->z * $distance
        );

        if(
            $hit->x < $position->x - 0.0001 ||
            $hit->x > $position->x + 1.0001 ||
            $hit->y < $position->y - 0.0001 ||
            $hit->y > $position->y + 1.0001 ||
            $hit->z < $position->z - 0.0001 ||
            $hit->z > $position->z + 1.0001
        ){
            return null;
        }

        return $hit;
    }

    private function getBoneBitForRemoval(
        PlayerInteractEvent $event,
        Player $player
    ) : ?int{
        $block = $event->getBlock();
        $mask = $block->getMask();

        $bit = $this->getBoneBit($event, $player);

        if($bit === null){
            return null;
        }

        if(($mask & (1 << $bit)) !== 0){
            return $bit;
        }

        $face = $event->getFace();

        $nextBit = match($face){
            Facing::UP, Facing::DOWN =>
                $bit < 4 ? $bit + 4 : $bit - 4,

            Facing::NORTH, Facing::SOUTH =>
                match($bit % 4){
                    0 => $bit + 1,
                    1 => $bit - 1,
                    2 => $bit + 1,
                    3 => $bit - 1
                },

            Facing::WEST, Facing::EAST =>
                match($bit % 4){
                    0, 1 => $bit + 2,
                    2, 3 => $bit - 2
                },

            default => null
        };

        if($nextBit === null){
            return null;
        }

        return ($mask & (1 << $nextBit)) !== 0
            ? $nextBit
            : null;
    }
}