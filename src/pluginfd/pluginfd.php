<?php

declare(strict_types=1);

namespace pluginfd;

use pocketmine\plugin\PluginBase;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\CustomiesItemFactory;

use pluginfd\block\TrashBlock;
use pluginfd\item\SocialButton;
use pluginfd\listener\DebugListenerBlock;
use pluginfd\listener\VoxelPlaceListener;
use pluginfd\registration\BlockRegistrar;
use pluginfd\registration\ResourceBlockRegistrar;
use pluginfd\registration\TreeBlockRegistrar;
use pluginfd\variant\ResourceVoxelCatalog;
use pluginfd\variant\TreeVariantCatalog;
use pluginfd\variant\VoxelCatalog;

use customiesdevs\customies\block\CustomiesBlockFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeIds;

class pluginfd extends PluginBase{

    protected function onEnable(): void{
		$materials = [
			"grass" 		=> ["name" => "Grass", 			"hardness" => 0.5, "gravity" => false,	"voxel"=>"cubev"],
			"stone" 		=> ["name" => "Stone", 			"hardness" => 1.5, "gravity" => false,	"voxel"=>"cubev"],
			"sandstone_a" 	=> ["name" => "Sandstone_a", 	"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered"],
			"sandstone_b" 	=> ["name" => "Sandstone_b", 	"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered"],
			"sandstone_c" 	=> ["name" => "Sandstone_c", 	"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered"],
			"sandstone_d" 	=> ["name" => "Sandstone_d", 	"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered"]
		];
		
/*		$materials = [
			"grass" 		=> ["name" => "Grass", 			"hardness" => 0.5, "gravity" => false],
			"dirt" 			=> ["name" => "Dirt", 			"hardness" => 0.5, "gravity" => false],
			"sand_white" 	=> ["name" => "Sand White", 	"hardness" => 0.2, "gravity" => true],
 			"sand_beige" 	=> ["name" => "Sand Beige", 	"hardness" => 0.2, "gravity" => true],
			"sand_orange" 	=> ["name" => "Sand Orange", 	"hardness" => 0.2, "gravity" => true],
			
			"gravel" 		=> ["name" => "Gravel", 		"hardness" => 0.3, "gravity" => true], 
			
			"granit_a" 		=> ["name" => "Granit_a", 		"hardness" => 0.3, "gravity" => false],
 			"granit_b" 		=> ["name" => "Granit_b", 		"hardness" => 0.3, "gravity" => false],
			"granit_c" 		=> ["name" => "Granit_c", 		"hardness" => 0.3, "gravity" => false],
			"granit_d" 		=> ["name" => "Granit_d", 		"hardness" => 0.3, "gravity" => false],
			"granit_e" 		=> ["name" => "Granit_e", 		"hardness" => 0.3, "gravity" => false],
			"granit_f" 		=> ["name" => "Granit_f", 		"hardness" => 0.3, "gravity" => false],
			"granit_g" 		=> ["name" => "Granit_g", 		"hardness" => 0.3, "gravity" => false],
			"granit_h" 		=> ["name" => "Granit_h", 		"hardness" => 0.3, "gravity" => false],
			"granit_i" 		=> ["name" => "Granit_i", 		"hardness" => 0.3, "gravity" => false],
			"granit_j" 		=> ["name" => "Granit_j", 		"hardness" => 0.3, "gravity" => false],
			"granit_k" 		=> ["name" => "Granit_k", 		"hardness" => 0.3, "gravity" => false],
			
			"calcite_a" 	=> ["name" => "Calcite_a", 		"hardness" => 0.3, "gravity" => false],
			"calcite_b" 	=> ["name" => "Calcite_b", 		"hardness" => 0.3, "gravity" => false],
			"calcite_c" 	=> ["name" => "Calcite_c", 		"hardness" => 0.3, "gravity" => false],
			"calcite_d" 	=> ["name" => "Calcite_d", 		"hardness" => 0.3, "gravity" => false],
			"calcite_e" 	=> ["name" => "Calcite_e", 		"hardness" => 0.3, "gravity" => false],
			"calcite_f" 	=> ["name" => "Calcite_f", 		"hardness" => 0.3, "gravity" => false],
			
			"basalt_a" 		=> ["name" => "Basalt_a", 		"hardness" => 0.3, "gravity" => false],
			"basalt_b" 		=> ["name" => "Basalt_b", 		"hardness" => 0.3, "gravity" => false],
			"basalt_c" 		=> ["name" => "Basalt_c", 		"hardness" => 0.3, "gravity" => false],
			
			"tuff_a" 		=> ["name" => "Tuff_a", 		"hardness" => 0.3, "gravity" => false],
			"tuff_b" 		=> ["name" => "Tuff_b", 		"hardness" => 0.3, "gravity" => false],
			"tuff_c" 		=> ["name" => "Tuff_c", 		"hardness" => 0.3, "gravity" => false],
			"tuff_d" 		=> ["name" => "Tuff_d", 		"hardness" => 0.3, "gravity" => false],
			"tuff_e" 		=> ["name" => "Tuff_e", 		"hardness" => 0.3, "gravity" => false],
			
			"stone" 		=> ["name" => "Stone", 			"hardness" => 1.5, "gravity" => false],
			"ferrum" 		=> ["name" => "Ferrum", 		"hardness" => 1.5, "gravity" => false]
		]; */
		
		$materialsres = [
			"stone" 		=> ["name" => "Stone", 			"hardness" => 1.5, "gravity" => false]
		];
		
		$treeMaterials=[
			"oak"	=>	["name"	=>	"Oak",	"hardness"	=>	1.5,	"gravity"	=>	false,	"stripped"	=>	true]
		];
		
		$resources = [
			"ferrum" => ["name" => "Ferrum"]
			// ,"cuprum" => ["name" => "Cuprum"],
			// "aurum" => ["name" => "Aurum"]
		];
		
		$this->getServer()->getPluginManager()->registerEvents(
            new DebugListenerBlock($this),
            $this
        );

        // Обычный кастомный блок
        CustomiesBlockFactory::getInstance()->registerBlock(
            static function(): TrashBlock{
                return new TrashBlock(new BlockIdentifier(BlockTypeIds::newId()));
            },
            "pluginfd:trash_block"
        );
		
		$progress = fn(string $message) => $this->getLogger()->info($message);

		// Регистрация воксельных блоков.
		BlockRegistrar::registerFamily(
			"voxel",
			$materials,
			$progress,
			50
		);

/* 		// Регистрация ресурсных воксельных блоков.
		ResourceBlockRegistrar::registerFamily(
			"resource",
			$materialsres,
			$resources,
			ResourceVoxelCatalog::getVariants(),
			$progress,
			10
		); */
		
		// Регистрция блоков дерева
		TreeBlockRegistrar::registerFamily(
			"tree",
			$treeMaterials,
			TreeVariantCatalog::getVariants(),
			$progress,
			10
		);
		
		// Установка воксельных блоков
		$this->getServer()->getPluginManager()->registerEvents(
			new VoxelPlaceListener(),
			$this
		);

        // Кастомный предмет
        CustomiesItemFactory::getInstance()->registerItem(
            static function(): SocialButton{
                return new SocialButton(new ItemIdentifier(ItemTypeIds::newId()));
            },
            "pluginfd:social_button",
            new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_ITEMS)
        );

        $this->getLogger()->info("Custom blocks and items registered!");
    }
}