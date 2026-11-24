<?php

declare(strict_types=1);

namespace pluginfd\registration;

use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\CustomiesItemFactory;

use pluginfd\block\ConfiguredBlock;
use pluginfd\item\VoxelPlaceItem;
use pluginfd\material\BlockMaterialCatalog;

use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeIds;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;

final class BlockRegistrar{

    public static function registerFamily(
        string $family,
        array $materials,
        array $variants,
        ?callable $progress = null,
        int $logEvery = 50
    ): void{
        $total = count($materials) * count($variants);
        $registered = 0;
        $startTime = microtime(true);
        $progress?->__invoke("Начало регистрации семейства {$family}. Всего блоков: {$total}");
        foreach($materials as $materialKey => $materialData){
            $progress?->__invoke("Обработка материала: {$materialKey}");
            foreach($variants as $variant){
                $blockIdentifier = "pluginfd:".$family."_".$materialKey."_".$variant->suffix;
                $blockName = $materialData["name"]." ".$variant->suffix;
                $geometry = $variant->geometry;
                $hardness = (float) $materialData["hardness"];
                $variantSuffix = $variant->suffix;
                $gravity = (bool) ($materialData["gravity"] ?? false);
                $blockId = BlockTypeIds::newId();
                CustomiesBlockFactory::getInstance()->registerBlock(
                    static function() use ($blockId,$blockName,$geometry,$materialKey,$hardness,$variantSuffix,$gravity): ConfiguredBlock{
                        return new ConfiguredBlock(
                            new BlockIdentifier($blockId),
                            $blockName,
                            $geometry,
                            BlockMaterialCatalog::create($materialKey, $variantSuffix),
                            $hardness,
                            $gravity
                        );
                    },
                    $blockIdentifier,
                    new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_ALL)
                );
                $itemIdentifier = "pluginfd:place_".$family."_".$materialKey."_".$variantSuffix;
                $itemId = ItemTypeIds::newId();
                $icon = $blockIdentifier;
                CustomiesItemFactory::getInstance()->registerItem(
                    static function() use ($itemId,$blockName,$icon,$blockIdentifier): VoxelPlaceItem{
                        return new VoxelPlaceItem(
                            new ItemIdentifier($itemId),
                            $blockName,
                            $icon,
                            $blockIdentifier
                        );
                    },
                    $itemIdentifier,
                    new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_CONSTRUCTION)
                );
                ++$registered;
                if($logEvery > 0 && $registered % $logEvery === 0){
                    $elapsed = round(microtime(true) - $startTime, 2);
                    $progress?->__invoke("[{$family}] {$registered}/{$total} | {$materialKey} | {$elapsed} сек.");
                }
            }
        }
        $elapsed = round(microtime(true) - $startTime, 2);
        $progress?->__invoke("Регистрация {$family} завершена: {$registered}/{$total}. Время: {$elapsed} сек.");
    }
}