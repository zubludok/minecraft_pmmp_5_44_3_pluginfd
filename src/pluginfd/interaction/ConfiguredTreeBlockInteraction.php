<?php

declare(strict_types=1);

namespace pluginfd\interaction;

use pluginfd\block\ConfiguredTreeBlock;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;

final class ConfiguredTreeBlockInteraction{

    /**
     * Обрабатывает взаимодействие с деревом.
     */
    public function handle(PlayerInteractEvent $event): void{

        if($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK){
            return;
        }

        $block = $event->getBlock();

        if(!$block instanceof ConfiguredTreeBlock){
            return;
        }

        $tool = $this->getTool($event->getItem());

		if($tool === "axe" && $block->hasStrippedMaterial()){
			$block->setBark(!$block->hasBark());

			$event->cancel();

			$block->getPosition()->getWorld()->setBlock(
				$block->getPosition(),
				$block
			);

			return;
		}

        foreach($block->getDebugActions() as $debugAction){

            if($debugAction["tool"] !== $tool){
                continue;
            }

            if(!$this->applyAction($block, $debugAction)){
                continue;
            }

            $event->cancel();

            $block->getPosition()->getWorld()->setBlock(
                $block->getPosition(),
                $block
            );

            return;
        }
    }

    /**
     * Определяет тип инструмента.
     */
    private function getTool(Item $item): ?string{

        $itemId = $item->getTypeId();

        // Палка.
        if($itemId === VanillaItems::STICK()->getTypeId()){
            return "stick";
        }

        // Топоры.
        $axes = [
            VanillaItems::WOODEN_AXE(),
            VanillaItems::STONE_AXE(),
            VanillaItems::IRON_AXE(),
            VanillaItems::GOLDEN_AXE(),
            VanillaItems::DIAMOND_AXE(),
            VanillaItems::NETHERITE_AXE()
        ];

        foreach($axes as $axe){
            if($itemId === $axe->getTypeId()){
                return "axe";
            }
        }

        // Лопаты.
        $shovels = [
            VanillaItems::WOODEN_SHOVEL(),
            VanillaItems::STONE_SHOVEL(),
            VanillaItems::IRON_SHOVEL(),
            VanillaItems::GOLDEN_SHOVEL(),
            VanillaItems::DIAMOND_SHOVEL(),
            VanillaItems::NETHERITE_SHOVEL()
        ];

        foreach($shovels as $shovel){
            if($itemId === $shovel->getTypeId()){
                return "shovel";
            }
        }

        // Мечи.
        $swords = [
            VanillaItems::WOODEN_SWORD(),
            VanillaItems::STONE_SWORD(),
            VanillaItems::IRON_SWORD(),
            VanillaItems::GOLDEN_SWORD(),
            VanillaItems::DIAMOND_SWORD(),
            VanillaItems::NETHERITE_SWORD()
        ];

        foreach($swords as $sword){
            if($itemId === $sword->getTypeId()){
                return "sword";
            }
        }

        return null;
    }

    /**
     * Выполняет действие, описанное в конфигурации группы.
     *
     * @param array{
     *     tool: string,
     *     action: string,
     *     state: string,
     *     min: int,
     *     max: int
     * } $debugAction
     */
    private function applyAction(
        ConfiguredTreeBlock $block,
        array $debugAction
    ): bool{

        $state = $debugAction["state"];
        $action = $debugAction["action"];

        $current = $block->getStateValue($state);

        switch($action){

            case "cycle":

                if(!is_int($current)){
                    return false;
                }

                $min = $debugAction["min"];
                $max = $debugAction["max"];

                $next = $current >= $max
                    ? $min
                    : $current + 1;

                $block->setState($state, $next);

                return true;

            case "toggle":

                if(!is_bool($current)){
                    return false;
                }

                $block->setState($state, !$current);

                return true;

            case "toggle_all":

                if(!is_int($current)){
                    return false;
                }

                $min = $debugAction["min"];
                $max = $debugAction["max"];

                // Если все кости включены — выключаем их.
                // Иначе включаем все.
                $next = $current === $max
                    ? $min
                    : $max;

                $block->setState($state, $next);

                return true;
        }

        return false;
    }
}