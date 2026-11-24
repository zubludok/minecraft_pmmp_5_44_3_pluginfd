<?php

declare(strict_types=1);

namespace pluginfd\interaction;

use pluginfd\block\ConfiguredResourceBlock;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

final class ConfiguredResourceBlockInteraction{

    public function handle(PlayerInteractEvent $event) : void{
        $block = $event->getBlock();
        if(!$block instanceof ConfiguredResourceBlock){
            return;
        }
        $player = $event->getPlayer();
        if($event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK){
            $this->handleLeftClick($event, $block, $player);
            return;
        }
        if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
            $this->handleRightClick($event, $block, $player);
        }
    }

    /* Удаление вокселя или скрытие залежи. */
    private function handleLeftClick(
        PlayerInteractEvent $event,
        ConfiguredResourceBlock $block,
        Player $player
    ) : void{
        $event->cancel();
        $hit = $this->getNearestHit($block, $player);
        if($hit === null){
            return;
        }
        // Залежь оказалась ближе обычных вокселей.
        if($hit["type"] === "resource"){
            $newBlock = clone $block;
            $newBlock->setResourceVisible(false);
            // Если вокселей уже нет — удаляем весь блок.
            if($newBlock->getMask() === 0){
                $player->getWorld()->setBlock($block->getPosition(),VanillaBlocks::AIR());
            }else{
                $player->getWorld()->setBlock($block->getPosition(),$newBlock);
            }
            $player->sendMessage("§cResource hidden");
            return;
        }
        // Попали в обычный воксель.
		$bit = $hit["bit"];
        $mask = $block->getMask();
        if(($mask & (1 << $bit)) === 0){
            return;
        }
        $newMask = $mask & ~(1 << $bit);
        // Если вокселей больше нет, проверяем наличие залежи.
        if($newMask === 0 && !$block->hasResource()){
            $player->getWorld()->setBlock($block->getPosition(),VanillaBlocks::AIR());
            $player->sendMessage("§cResource block removed");
            return;
        }
        $newBlock = clone $block;
        $newBlock->setMask($newMask);
        $player->getWorld()->setBlock($block->getPosition(),$newBlock);
        $player->sendMessage("§cRemoved bone: §f" . $bit);
    }

    /* Добавление вокселя. */
    private function handleRightClick(
        PlayerInteractEvent $event,
        ConfiguredResourceBlock $block,
        Player $player
    ) : void{
        $materialKey = $block->getBaseMaterialKey();
        $materialItem = match($materialKey){
            "grass" => VanillaBlocks::GRASS()->asItem(),
            "dirt" => VanillaBlocks::DIRT()->asItem(),
            "sand_white", "sand_beige", "sand_orange" => VanillaBlocks::SAND()->asItem(),
            "stone" => VanillaBlocks::STONE()->asItem(),
            default => null
        };
        $item = $event->getItem();
        $player->sendMessage("§eBase material: §f" . $materialKey);
        $player->sendMessage("§eHeld item: §f" . $item->getName());
        if($materialItem === null){
            $player->sendMessage("§cUnknown resource block material!");
            return;
        }
        if(!$item->equals($materialItem)){
            $player->sendMessage("§cItem mismatch!");
            return;
        }
        $player->sendMessage("§aItem matched!");
        $touch = $event->getTouchVector();
        $position = $block->getPosition();
        $hit = new Vector3(
            $position->x + $touch->x,
            $position->y + $touch->y,
            $position->z + $touch->z
        );
        $bit = $this->getBoneBitFromHit($hit, $position);
        if($bit === null){
            return;
        }
        $mask = $block->getMask();
        // Если воксель уже существует, оставляем стандартное поведение.
        if(($mask & (1 << $bit)) !== 0){
            return;
        }
        $event->cancel();
        $newBlock = clone $block;
        $newBlock->setMask($mask | (1 << $bit));
        $player->getWorld()->setBlock(
            $position,
            $newBlock
        );
        $player->sendMessage("§aAdded bone: §f" . $bit);
    }

    /* Возвращает ближайшее пересечение луча:
     * resource или voxel.
     *  @return array{type:string, point:Vector3, distance:float, bit?:int}|null */
    private function getNearestHit(
        ConfiguredResourceBlock $block,
        Player $player
    ) : ?array{
        $position = $block->getPosition();
        $origin = $player->getEyePos();
        $direction = $player->getDirectionVector();
        $nearest = null;
        // Проверяем залежь.
        if($block->hasResource()){
            $size = $block->getResourceSize();
            $min = (1.0 - $size) / 2.0;
            $max = (1.0 + $size) / 2.0;
            $distance = $this->rayBoxIntersection(
                $origin,
                $direction,
                $position->x + $min,
                $position->y + $min,
                $position->z + $min,
                $position->x + $max,
                $position->y + $max,
                $position->z + $max
            );
            if($distance !== null){
				$nearest = [
					"type" => "resource",
					"point" => $origin->add(
						$direction->x * $distance,
						$direction->y * $distance,
						$direction->z * $distance
					),
					"distance" => $distance
				];
            }
        }
        // Проверяем все существующие воксели.
        $mask = $block->getMask();
        for($bit = 0; $bit < 8; ++$bit){
            if(($mask & (1 << $bit)) === 0){
                continue;
            }
            $upper = $bit < 4;
            $corner = $bit % 4;
			$east = $corner === 0 || $corner === 1;
			$south = $corner === 1 || $corner === 3;
            $minX = $east ? 0.5 : 0.0;
            $maxX = $east ? 1.0 : 0.5;
            $minY = $upper ? 0.5 : 0.0;
            $maxY = $upper ? 1.0 : 0.5;
            $minZ = $south ? 0.5 : 0.0;
            $maxZ = $south ? 1.0 : 0.5;
            $distance = $this->rayBoxIntersection(
                $origin,
                $direction,
                $position->x + $minX,
                $position->y + $minY,
                $position->z + $minZ,
                $position->x + $maxX,
                $position->y + $maxY,
                $position->z + $maxZ
            );
            if($distance === null){
                continue;
            }
			if($nearest === null || $distance < $nearest["distance"]){
				$nearest = [
					"type" => "voxel",
					"bit" => $bit,
					"point" => $origin->add(
						$direction->x * $distance,
						$direction->y * $distance,
						$direction->z * $distance
					),
					"distance" => $distance
				];
			}
        }
        return $nearest;
    }

    /* Пересечение луча с Axis Aligned Bounding Box.
     * Возвращает расстояние до ближайшей точки пересечения. */
    private function rayBoxIntersection(
        Vector3 $origin,
        Vector3 $direction,
        float $minX,
        float $minY,
        float $minZ,
        float $maxX,
        float $maxY,
        float $maxZ
    ) : ?float{
        $tMin = -INF;
        $tMax = INF;
        foreach([
            [$origin->x, $direction->x, $minX, $maxX],
            [$origin->y, $direction->y, $minY, $maxY],
            [$origin->z, $direction->z, $minZ, $maxZ]
        ] as [$originAxis, $directionAxis, $min, $max]){
            if(abs($directionAxis) < 0.000001){
                if($originAxis < $min || $originAxis > $max){
                    return null;
                }
                continue;
            }
            $t1 = ($min - $originAxis) / $directionAxis;
            $t2 = ($max - $originAxis) / $directionAxis;
            $axisMin = min($t1, $t2);
            $axisMax = max($t1, $t2);
            $tMin = max($tMin, $axisMin);
            $tMax = min($tMax, $axisMax);
            if($tMin > $tMax){
                return null;
            }
        }
        if($tMax < 0){
            return null;
        }
        return max(0.0, $tMin);
    }

    /* Определяет номер вокселя по точке попадания. */
    private function getBoneBitFromHit(Vector3 $hit,Vector3 $position) : ?int{
        $localX = $hit->x - $position->x;
        $localY = $hit->y - $position->y;
        $localZ = $hit->z - $position->z;
        $upper = $localY >= 0.5;
        $east = $localX >= 0.5;
        $south = $localZ >= 0.5;
        $corner = match(true){
			!$east && !$south => 0,
			!$east && $south => 1,
			$east && !$south => 2,
			$east && $south => 3
        };
        return $upper ? $corner : $corner + 4;
    }
}