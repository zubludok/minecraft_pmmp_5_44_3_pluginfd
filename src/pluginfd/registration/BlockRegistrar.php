<?php

declare(strict_types=1);

namespace pluginfd\registration;

use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\CustomiesItemFactory;

use pluginfd\block\ConfiguredBlock;
use pluginfd\block\VoxelPlaceholderBlock;
use pluginfd\item\VoxelPlaceItem;
use pluginfd\material\BlockMaterialCatalog;
use pluginfd\variant\VoxelCatalog;
use pluginfd\catalog\BlockCatalog;

use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeIds;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;

final class BlockRegistrar{

    public static function registerFamily(
        string $family,
        array $materials,
        ?callable $progress=null,
        int $logEvery=50
    ): void{
        $total=count($materials);
        $registered=0;
        $startTime=microtime(true);

        $progress?->__invoke("Начало регистрации семейства {$family}. Всего блоков: {$total}");

        foreach($materials as $materialKey=>$materialData){
            $progress?->__invoke("Обработка материала: {$materialKey}");

            $voxelType=strtolower(trim((string)($materialData["voxel"]??"")));

            if($voxelType===""){
                throw new \InvalidArgumentException("Voxel type is not defined for material: ".$materialKey);
            }

			$placement=isset($materialData["placement"])
				?strtolower(trim((string)$materialData["placement"]))
				:null;

			if($placement!==null && !in_array($placement,["block","icon"],true)){
				throw new \InvalidArgumentException("Unknown placement type: ".$placement);
			}

            $profile=VoxelCatalog::getProfile($voxelType);
            $geometry=$profile["geometry"];

            $blockIdentifier="pluginfd:".$family."_".$materialKey."_".$voxelType;
            $blockName=$materialData["name"]." ".$voxelType;
            $hardness=(float)$materialData["hardness"];
            $gravity=(bool)($materialData["gravity"]??false);

            $blockId=BlockTypeIds::newId();

            CustomiesBlockFactory::getInstance()->registerBlock(
                static function() use(
                    $blockId,
                    $blockName,
                    $geometry,
                    $materialKey,
                    $hardness,
                    $gravity,
                    $voxelType
                ): ConfiguredBlock{
                    return new ConfiguredBlock(
                        new BlockIdentifier($blockId),
                        $blockName,
                        $geometry,
                        BlockMaterialCatalog::create($materialKey),
                        $hardness,
                        $gravity,
                        $materialKey,
                        $voxelType
                    );
                },
                $blockIdentifier,
                new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_ALL)
            );
			
			BlockCatalog::register($blockIdentifier,[
				"family"=>$family,
				"material"=>$materialKey,
				"voxel"=>$voxelType,
				"geometry"=>$geometry
			]);
			
            if($placement==="icon"){
                $itemIdentifier="pluginfd:place_".$family."_".$materialKey."_".$voxelType;
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
                $placeholderIdentifier="pluginfd:placeholder_".$family."_".$materialKey."_".$voxelType;
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
				// Привязка заглушки к настоящему блоку
				VoxelPlaceholderBlock::bind(
					CustomiesItemFactory::getInstance()->get($placeholderIdentifier)->getTypeId(),
					$blockIdentifier
				);
            }

            ++$registered;

            if($logEvery>0 && $registered%$logEvery===0){
                $elapsed=round(microtime(true)-$startTime,2);
                $progress?->__invoke("[{$family}] {$registered}/{$total} | {$materialKey} ({$voxelType}, {$placement}) | {$elapsed} сек.");
            }
        }

        $elapsed=round(microtime(true)-$startTime,2);
        $progress?->__invoke("Регистрация {$family} завершена: {$registered}/{$total}. Время: {$elapsed} сек.");
    }
}