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
        ?callable $progress=null,
        int $logEvery=10
    ): void{
        $total=0;

        foreach($materials as $materialData){
            $multiplier=($materialData["stripped"] ?? false) ? 2 : 1;
            $total+=count($variants)*$multiplier;
        }

        $registered=0;
        $startTime=microtime(true);

        $progress?->__invoke(
            "Начало регистрации {$family}. Всего блоков: {$total}"
        );

        foreach($materials as $materialKey=>$materialData){
            $progress?->__invoke(
                "Обработка материала: {$materialKey}"
            );

            $hardness=(float)$materialData["hardness"];
            $gravity=(bool)($materialData["gravity"] ?? false);

            $blockMaterials=TreeMaterialCatalog::create($materialKey);
            $hasStripped=(bool)($materialData["stripped"] ?? false);

            $strippedMaterials=$hasStripped
                ? TreeMaterialCatalog::create($materialKey,true)
                : [];

            foreach($variants as $variantKey=>$variantData){
                $blockName=$materialData["name"]." ".$variantData["name"];
				$strippedBlockIdentifier=$hasStripped
					? "pluginfd:".$family."_".$materialKey."_".$variantKey."_stripped"
					: null;
                self::registerOne(
                    $family,
                    $materialKey,
                    $variantKey,
                    $variantData,
                    $blockName,
                    $hardness,
                    $gravity,
                    $blockMaterials,
                    "",
					$strippedBlockIdentifier
                );

                ++$registered;
                self::reportProgress(
                    $progress,
                    $registered,
                    $total,
                    $logEvery,
                    $startTime,
                    $family,
                    $materialKey,
                    $variantKey
                );

                if($hasStripped){
                    self::registerOne(
                        $family,
                        $materialKey,
                        $variantKey,
                        $variantData,
                        $blockName." без коры",
                        $hardness,
                        $gravity,
                        $strippedMaterials,
                        "_stripped",
						null
                    );

                    ++$registered;
                    self::reportProgress(
                        $progress,
                        $registered,
                        $total,
                        $logEvery,
                        $startTime,
                        $family,
                        $materialKey,
                        $variantKey."_stripped"
                    );
                }
            }
        }

        $elapsed=round(microtime(true)-$startTime,2);

        $progress?->__invoke(
            "Регистрация {$family} завершена: {$registered}/{$total}. ".
            "Время: {$elapsed} сек."
        );
    }

    private static function registerOne(
        string $family,
        string $materialKey,
        string $variantKey,
        array $variantData,
        string $blockName,
        float $hardness,
        bool $gravity,
        array $materials,
        string $suffix,
		?string $strippedBlockIdentifier
    ): void{
        $blockIdentifier=
            "pluginfd:".$family."_".$materialKey."_".$variantKey.$suffix;

        $blockId=BlockTypeIds::newId();

        CustomiesBlockFactory::getInstance()->registerBlock(
            static function() use(
                $blockId,
                $variantData,
                $blockName,
                $hardness,
                $gravity,
                $materials,
				$strippedBlockIdentifier
            ): ConfiguredTreeBlock{
                return new ConfiguredTreeBlock(
                    new BlockIdentifier($blockId),
                    [
                        ...$variantData,
                        "name"=>$blockName
                    ],
                    $materials,
                    $hardness,
                    $gravity,
					$strippedBlockIdentifier
                );
            },
            $blockIdentifier,
            new CreativeInventoryInfo(
                CreativeInventoryInfo::CATEGORY_ALL
            )
        );

        $itemIdentifier=
            "pluginfd:place_".$family."_".$materialKey."_".$variantKey.$suffix;

        $itemId=ItemTypeIds::newId();

        CustomiesItemFactory::getInstance()->registerItem(
            static function() use(
                $itemId,
                $blockName,
                $blockIdentifier
            ): VoxelPlaceItem{
                return new VoxelPlaceItem(
                    new ItemIdentifier($itemId),
                    $blockName,
                    $blockIdentifier,
                    $blockIdentifier
                );
            },
            $itemIdentifier,
            new CreativeInventoryInfo(
                CreativeInventoryInfo::CATEGORY_CONSTRUCTION
            )
        );
    }

    private static function reportProgress(
        ?callable $progress,
        int $registered,
        int $total,
        int $logEvery,
        float $startTime,
        string $family,
        string $materialKey,
        string $variantKey
    ): void{
        if($logEvery <= 0 || $registered % $logEvery !== 0){
            return;
        }

        $elapsed=round(microtime(true)-$startTime,2);

        $progress?->__invoke(
            "[{$family}] {$registered}/{$total} | ".
            "{$materialKey} + {$variantKey} | {$elapsed} сек."
        );
    }
}