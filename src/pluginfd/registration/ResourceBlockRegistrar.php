<?php

declare(strict_types=1);

namespace pluginfd\registration;

use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\CustomiesItemFactory;

use pluginfd\block\ConfiguredResourceBlock;
use pluginfd\block\VoxelPlaceholderBlock;
use pluginfd\item\VoxelPlaceItem;
use pluginfd\material\BlockMaterialCatalog;
use pluginfd\material\ResourceMaterialCatalog;

use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeIds;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;

final class ResourceBlockRegistrar{

    public static function registerFamily(
        string $family,
        array $materials,
        array $resourceVariants,
        ?callable $progress=null,
        int $logEvery=10
    ): void{
        $total=count($materials)*count($resourceVariants);
        $registered=0;
        $startTime=microtime(true);

        $progress?->__invoke("Начало регистрации {$family}. Всего блоков: {$total}");

        foreach($materials as $pairKey=>$pairData){
            $baseData=$pairData["base"]??[];
            $resourceData=$pairData["res"]??[];

            $materialKey=(string)($baseData["key"]??"");
            $resourceKey=(string)($resourceData["key"]??"");

            if($materialKey==="" || $resourceKey===""){
                throw new \InvalidArgumentException("Base or resource key is missing in pair: ".$pairKey);
            }

			$placement=isset($pairData["placement"])
				?strtolower(trim((string)$pairData["placement"]))
				:null;

			if($placement!==null && !in_array($placement,["block","icon"],true)){
				throw new \InvalidArgumentException("Unknown placement type: ".$placement);
			}

            $baseName=(string)($baseData["name"]??$materialKey);
            $resourceName=(string)($resourceData["name"]??$resourceKey);
            $hardness=(float)($baseData["hardness"]??1.0);
            $gravity=(bool)($baseData["gravity"]??false);

            $progress?->__invoke("Обработка сочетания: {$pairKey}");

            foreach($resourceVariants as $resourceVariant){
                $variantSuffix=$resourceVariant["suffix"];
                $blockIdentifier="pluginfd:".$family."_".$materialKey."_".$resourceKey."_".$variantSuffix;
                $blockName=$baseName." ".$resourceName." ".$variantSuffix;
                $geometry=$resourceVariant["geometry"];
                $resourceSize=(float)$resourceVariant["size"];
                $resourceStyle=$resourceVariant["style"];

                $blockStartedAt=microtime(true);
                $blockId=BlockTypeIds::newId();

                CustomiesBlockFactory::getInstance()->registerBlock(
                    static function() use(
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
                        return new ConfiguredResourceBlock(
                            new BlockIdentifier($blockId),
                            $blockName,
                            $geometry,
                            ResourceMaterialCatalog::create(
                                $materialKey,
                                $resourceKey,
                                $resourceStyle
                            ),
                            $hardness,
                            $gravity,
                            $resourceSize,
                            $materialKey
                        );
                    },
                    $blockIdentifier,
                    new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_ALL)
                );

                if($placement==="icon"){
                    $itemIdentifier="pluginfd:place_".$family."_".$materialKey."_".$resourceKey."_".$variantSuffix;
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
                        new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_CONSTRUCTION)
                    );
                }elseif($placement==="block"){
                    $placeholderIdentifier="pluginfd:placeholder_".$family."_".$materialKey."_".$resourceKey."_".$variantSuffix;
                    $placeholderId=BlockTypeIds::newId();

                    CustomiesBlockFactory::getInstance()->registerBlock(
                        static function() use(
                            $placeholderId,
                            $blockName,
                            $materialKey,
                            $hardness
                        ): VoxelPlaceholderBlock{
                            return new VoxelPlaceholderBlock(
                                new BlockIdentifier($placeholderId),
                                $blockName,
                                BlockMaterialCatalog::create($materialKey),
                                $hardness
                            );
                        },
                        $placeholderIdentifier,
                        new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_CONSTRUCTION)
                    );
					VoxelPlaceholderBlock::bind(
						CustomiesItemFactory::getInstance()->get($placeholderIdentifier)->getTypeId(),
						$blockIdentifier
					);
                }

                ++$registered;

                $totalMs=(microtime(true)-$blockStartedAt)*1000;
                $elapsed=round(microtime(true)-$startTime,2);

                $progress?->__invoke(sprintf(
                    "[{$family}] %d/%d | %s | %.2f мс | с начала: %.2f сек.",
                    $registered,
                    $total,
                    $blockIdentifier,
                    $totalMs,
                    $elapsed
                ));

                if($logEvery>0 && $registered%$logEvery===0){
                    $progress?->__invoke("[{$family}] {$registered}/{$total} | {$pairKey} | {$elapsed} сек.");
                }
            }
        }

        $elapsed=round(microtime(true)-$startTime,2);
        $progress?->__invoke("Регистрация {$family} завершена: {$registered}/{$total}. Время: {$elapsed} сек.");
    }
}