<?php

declare(strict_types=1);

namespace pluginfd\registration;

use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\CustomiesItemFactory;

use pluginfd\block\ConfiguredTreeBlock;
use pluginfd\item\VoxelPlaceItem;
use pluginfd\material\TreeMaterialCatalog;

use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeIds;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;

final class TreeBlockRegistrar{

    public static function registerFamily(
        string $family,
        array $materials,
        array $variants,
        ?callable $progress = null,
        int $logEvery = 10
    ): void{

        $total = count($materials) * count($variants);
        $registered = 0;
        $startTime = microtime(true);

        $progress?->__invoke(
            "Начало регистрации {$family}. Всего блоков: {$total}"
        );

        foreach($materials as $materialKey => $materialData){

            $progress?->__invoke(
                "Обработка материала: {$materialKey}"
            );

            foreach($variants as $variantKey => $variantData){

                $blockIdentifier =
                    "pluginfd:" . $family . "_" . $materialKey . "_" . $variantKey;

                $blockName =
                    $materialData["name"] . " " . $variantData["name"];

                $hardness = (float) $materialData["hardness"];
                $gravity = (bool) ($materialData["gravity"] ?? false);

                $blockId = BlockTypeIds::newId();

                CustomiesBlockFactory::getInstance()->registerBlock(
                    static function() use (
                        $blockId,
                        $variantData,
                        $blockName,
                        $hardness,
                        $gravity,
                        $materialKey
                    ): ConfiguredTreeBlock{

                        $blockMaterials = TreeMaterialCatalog::create(
                            $materialKey
                        );

                        return new ConfiguredTreeBlock(
                            new BlockIdentifier($blockId),
                            [
                                ...$variantData,
                                "name" => $blockName
                            ],
                            $blockMaterials,
                            $hardness,
                            $gravity
                        );
                    },
                    $blockIdentifier,
                    new CreativeInventoryInfo(
                        CreativeInventoryInfo::CATEGORY_ALL
                    )
                );

                $itemIdentifier =
                    "pluginfd:place_" . $family . "_" . $materialKey . "_" . $variantKey;

                $itemId = ItemTypeIds::newId();
                $icon = $blockIdentifier;

                CustomiesItemFactory::getInstance()->registerItem(
                    static function() use (
                        $itemId,
                        $blockName,
                        $icon,
                        $blockIdentifier
                    ): VoxelPlaceItem{

                        return new VoxelPlaceItem(
                            new ItemIdentifier($itemId),
                            $blockName,
                            $icon,
                            $blockIdentifier
                        );
                    },
                    $itemIdentifier,
                    new CreativeInventoryInfo(
                        CreativeInventoryInfo::CATEGORY_CONSTRUCTION
                    )
                );

                ++$registered;

                if($logEvery > 0 && $registered % $logEvery === 0){

                    $elapsed = round(
                        microtime(true) - $startTime,
                        2
                    );

                    $progress?->__invoke(
                        "[{$family}] {$registered}/{$total} | " .
                        "{$materialKey} + {$variantKey} | {$elapsed} сек."
                    );
                }
            }
        }

        $elapsed = round(
            microtime(true) - $startTime,
            2
        );

        $progress?->__invoke(
            "Регистрация {$family} завершена: {$registered}/{$total}. " .
            "Время: {$elapsed} сек."
        );
    }
}