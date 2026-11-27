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
			"dirt" 			=> ["name" => "Dirt", 			"hardness" => 0.5, "gravity" => false,	"voxel"=>"cubev"],
			"sand_white" 	=> ["name" => "Sand White", 	"hardness" => 0.2, "gravity" => true,	"voxel"=>"cubev"],
 			"sand_beige" 	=> ["name" => "Sand Beige", 	"hardness" => 0.2, "gravity" => true,	"voxel"=>"cubev"],
			"sand_orange" 	=> ["name" => "Sand Orange", 	"hardness" => 0.2, "gravity" => true,	"voxel"=>"cubev"],
			
			"gravel" 		=> ["name" => "Gravel", 		"hardness" => 0.3, "gravity" => true,	"voxel"=>"cubev"], 
			
			"sandstone_a" 	=> ["name" => "Sandstone_a", 	"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered"],
			"sandstone_b" 	=> ["name" => "Sandstone_b", 	"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered"],
			"sandstone_c" 	=> ["name" => "Sandstone_c", 	"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered"],
			"sandstone_d" 	=> ["name" => "Sandstone_d", 	"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered"],
			
			"granit_a" 		=> ["name" => "Granit_a", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cube"],
 			"granit_b" 		=> ["name" => "Granit_b", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cube"],
			"granit_c" 		=> ["name" => "Granit_c", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cube"],
			"granit_d" 		=> ["name" => "Granit_d", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cube"],
			"granit_e" 		=> ["name" => "Granit_e", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cube"],
			"granit_f" 		=> ["name" => "Granit_f", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cube"],
			"granit_g" 		=> ["name" => "Granit_g", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cube"],
			"granit_h" 		=> ["name" => "Granit_h", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cube"],
			"granit_i" 		=> ["name" => "Granit_i", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cube"],
			"granit_j" 		=> ["name" => "Granit_j", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cube"],
			"granit_k" 		=> ["name" => "Granit_k", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cube"],
			
			"calcite_a" 	=> ["name" => "Calcite_a", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cubev"],
			"calcite_b" 	=> ["name" => "Calcite_b", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cubev"],
			"calcite_c" 	=> ["name" => "Calcite_c", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cubev"],
			"calcite_d" 	=> ["name" => "Calcite_d", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cubev"],
			"calcite_e" 	=> ["name" => "Calcite_e", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cubev"],
			"calcite_f" 	=> ["name" => "Calcite_f", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cubev"],
			
			"shales_a" 		=> ["name" => "Shales_a", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered"],
			"shales_b" 		=> ["name" => "Shales_b", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered"],
			"shales_c" 		=> ["name" => "Shales_c", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered"],
			"shales_d" 		=> ["name" => "Shales_d", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered"],
			"shales_e" 		=> ["name" => "Shales_e", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered"],
			
			"basalt_a" 		=> ["name" => "Basalt_a", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"columnar"],
			"basalt_b" 		=> ["name" => "Basalt_b", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"columnar"],
			"basalt_c" 		=> ["name" => "Basalt_c", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"cubev"],
			"basalt_d" 		=> ["name" => "Basalt_d", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"cubev"],
			"basalt_e" 		=> ["name" => "Basalt_e", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"columnar"],
			
			"marble_a" 		=> ["name" => "Marble_a", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"cuber"],
			"marble_b" 		=> ["name" => "Marble_b", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"cuber"],
			"marble_c" 		=> ["name" => "Marble_c", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"cuber"],
			"marble_d" 		=> ["name" => "Marble_d", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"cuber"],
			"marble_e" 		=> ["name" => "Marble_e", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"cuber"],
			"marble_f" 		=> ["name" => "Marble_f", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"cuber"],
			
			"tuff_a" 		=> ["name" => "Tuff_a", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cubev"],
			"tuff_b" 		=> ["name" => "Tuff_b", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cubev"],
			"tuff_c" 		=> ["name" => "Tuff_c", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cubev"],
			"tuff_d" 		=> ["name" => "Tuff_d", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cubev"],
			"tuff_e" 		=> ["name" => "Tuff_e", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cubev"],
			
			"ferrum" 		=> ["name" => "Ferrum", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"cubev"]
		];
		
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
/* 		TreeBlockRegistrar::registerFamily(
			"tree",
			$treeMaterials,
			TreeVariantCatalog::getVariants(),
			$progress,
			10
		); */
		
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