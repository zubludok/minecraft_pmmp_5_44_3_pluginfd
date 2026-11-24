<?php

declare(strict_types=1);

namespace pluginfd\registration;

use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\CustomiesItemFactory;

use pluginfd\block\ConfiguredResourceBlock;
use pluginfd\item\VoxelPlaceItem;
use pluginfd\material\ResourceMaterialCatalog;

use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeIds;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;

final class ResourceBlockRegistrar{

    public static function registerFamily(
        string $family,
        array $materials,
        array $resources,
        array $resourceVariants,
        ?callable $progress = null,
        int $logEvery = 10
    ): void{
        $total = count($materials) * count($resources) * count($resourceVariants);
        $registered = 0;
        $startTime = microtime(true);
        $progress?->__invoke("Начало регистрации {$family}. Всего блоков: {$total}");
        foreach($materials as $materialKey => $materialData){
            $progress?->__invoke("Обработка материала: {$materialKey}");
            foreach($resources as $resourceKey => $resourceData){
                $progress?->__invoke("Ресурс: {$resourceKey} | Основа: {$materialKey}");
                foreach($resourceVariants as $resourceVariant){
                    $variantSuffix = $resourceVariant["suffix"];
                    $blockIdentifier ="pluginfd:".$family."_".$materialKey."_".$resourceKey."_".$variantSuffix;
                    $blockName = $materialData["name"]." ".$resourceData["name"]." ".$variantSuffix;
                    $geometry = $resourceVariant["geometry"];
                    $hardness = (float) $materialData["hardness"];
                    $gravity = (bool) ($materialData["gravity"] ?? false);
                    $resourceSize = (float) $resourceVariant["size"];
                    $resourceStyle = $resourceVariant["style"];
					$blockStartedAt=microtime(true);
                    $blockId = BlockTypeIds::newId();
					$blockRegistrationStartedAt=microtime(true);
					CustomiesBlockFactory::getInstance()->registerBlock(
						static function() use (
							$blockId,
							$blockName,
							$geometry,
							$hardness,
							$gravity,
							$resourceSize,
							$materialKey,
							$resourceKey,
							$resourceStyle
						): ConfiguredResourceBlock{
							$blockMaterials = ResourceMaterialCatalog::create(
								$materialKey,
								$resourceKey,
								$resourceStyle
							);
							return new ConfiguredResourceBlock(
								new BlockIdentifier($blockId),
								$blockName,
								$geometry,
								$blockMaterials,
								$hardness,
								$gravity,
								$resourceSize,
								$materialKey
							);
						},
						$blockIdentifier,
						new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_ALL)
					);
					$blockRegistrationMs=(microtime(true)-$blockRegistrationStartedAt)*1000;
                    $itemIdentifier = "pluginfd:place_".$family."_".$materialKey."_".$resourceKey."_".$variantSuffix;
                    $itemId = ItemTypeIds::newId();
                    $icon = $blockIdentifier;
					$itemRegistrationStartedAt=microtime(true);
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
                        new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_CONSTRUCTION)
                    );
					$itemRegistrationMs=(microtime(true)-$itemRegistrationStartedAt)*1000;
                    ++$registered;
					$totalMs=(microtime(true)-$blockStartedAt)*1000;
					$elapsed=round(microtime(true)-$startTime,2);

					$progress?->__invoke(sprintf(
						"[{$family}] %d/%d | %s | блок: %.2f мс | предмет: %.2f мс | всего: %.2f мс | с начала: %.2f сек.",
						$registered,
						$total,
						$blockIdentifier,
						$blockRegistrationMs,
						$itemRegistrationMs,
						$totalMs,
						$elapsed
					));
                    if($logEvery > 0 && $registered % $logEvery === 0){
                        $elapsed = round(microtime(true) - $startTime, 2);
                        $progress?->__invoke("[{$family}] {$registered}/{$total} | "."{$materialKey} + {$resourceKey} | "."{$elapsed} сек.");
                    }
                }
            }
        }
        $elapsed = round(microtime(true) - $startTime, 2);
        $progress?->__invoke("Регистрация {$family} завершена: {$registered}/{$total}. Время: {$elapsed} сек.");
    }
}