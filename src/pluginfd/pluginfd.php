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
			"grass" 		=> ["name" => "Grass", 			"hardness" => 0.5, "gravity" => false,	"voxel"=>"cubev", "placement"=>"block"]/* ,
			"stone" 		=> ["name" => "Stone", 			"hardness" => 1.5, "gravity" => false,	"voxel"=>"cubev", "placement"=>"block"],
			"dirt" 			=> ["name" => "Dirt", 			"hardness" => 0.5, "gravity" => false,	"voxel"=>"cubev", "placement"=>"block"],
			"sand_white" 	=> ["name" => "Sand White", 	"hardness" => 0.2, "gravity" => true,	"voxel"=>"cubev", "placement"=>"block"],
 			"sand_beige" 	=> ["name" => "Sand Beige", 	"hardness" => 0.2, "gravity" => true,	"voxel"=>"cubev", "placement"=>"block"],
			"sand_orange" 	=> ["name" => "Sand Orange", 	"hardness" => 0.2, "gravity" => true,	"voxel"=>"cubev", "placement"=>"block"],
			
			"gravel" 		=> ["name" => "Gravel", 		"hardness" => 0.3, "gravity" => true,	"voxel"=>"cubev", "placement"=>"block"],
			
			"sandstone_a" 	=> ["name" => "Sandstone_a", 	"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered", "placement"=>"block"],
			"sandstone_b" 	=> ["name" => "Sandstone_b", 	"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered", "placement"=>"block"],
			"sandstone_c" 	=> ["name" => "Sandstone_c", 	"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered", "placement"=>"block"],
			"sandstone_d" 	=> ["name" => "Sandstone_d", 	"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered", "placement"=>"block"],
			
			"andesite_a" 	=> ["name" => "Andesite_a", 	"hardness" => 0.3, "gravity" => true,	"voxel"=>"cubev", "placement"=>"block"],
			"andesite_b" 	=> ["name" => "Andesite_b", 	"hardness" => 0.3, "gravity" => true,	"voxel"=>"cubev", "placement"=>"block"],
			"andesite_c" 	=> ["name" => "Andesite_c", 	"hardness" => 0.3, "gravity" => true,	"voxel"=>"cubev", "placement"=>"block"],
			"andesite_d" 	=> ["name" => "Andesite_d", 	"hardness" => 0.3, "gravity" => true,	"voxel"=>"cubev", "placement"=>"block"],
			
			"basalt_a" 		=> ["name" => "Basalt_a", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"columnar", "placement"=>"block"],
			"basalt_b" 		=> ["name" => "Basalt_b", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"columnar", "placement"=>"block"],
			"basalt_c" 		=> ["name" => "Basalt_c", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"cubev", "placement"=>"block"],
			"basalt_d" 		=> ["name" => "Basalt_d", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"cubev", "placement"=>"block"],
			"basalt_e" 		=> ["name" => "Basalt_e", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"columnar", "placement"=>"block"],
			
			"calcite_a" 	=> ["name" => "Calcite_a", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cube"],
			"calcite_b" 	=> ["name" => "Calcite_b", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cube"],
			"calcite_c" 	=> ["name" => "Calcite_c", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cube"],
			"calcite_d" 	=> ["name" => "Calcite_d", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"columnar", "placement"=>"block"],
			"calcite_e" 	=> ["name" => "Calcite_e", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"columnar", "placement"=>"block"],
			"calcite_f" 	=> ["name" => "Calcite_f", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"columnar", "placement"=>"block"],
			
			"diorite_a" 	=> ["name" => "Diorite_a", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cube"],
			"diorite_b" 	=> ["name" => "Diorite_b", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cube"],
			"diorite_c" 	=> ["name" => "Diorite_c", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cube"],
			"diorite_d" 	=> ["name" => "Diorite_d", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cube"],
			
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
			
			"limestone_a" 	=> ["name" => "Limestone_a", 	"hardness" => 0.3, "gravity" => false,	"voxel"=>"cubev", "placement"=>"block"],
			"limestone_b" 	=> ["name" => "Limestone_b", 	"hardness" => 0.3, "gravity" => false,	"voxel"=>"cubev", "placement"=>"block"],
			"limestone_c" 	=> ["name" => "Limestone_c", 	"hardness" => 0.3, "gravity" => false,	"voxel"=>"cubev", "placement"=>"block"],
			"limestone_d" 	=> ["name" => "Limestone_d", 	"hardness" => 0.3, "gravity" => false,	"voxel"=>"cubev", "placement"=>"block"],
			
			"marble_a" 		=> ["name" => "Marble_a", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"cuber", "placement"=>"block"],
			"marble_b" 		=> ["name" => "Marble_b", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"cuber", "placement"=>"block"],
			"marble_c" 		=> ["name" => "Marble_c", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"cuber", "placement"=>"block"],
			"marble_d" 		=> ["name" => "Marble_d", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"cuber", "placement"=>"block"],
			"marble_e" 		=> ["name" => "Marble_e", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"cuber", "placement"=>"block"],
			"marble_f" 		=> ["name" => "Marble_f", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"cuber", "placement"=>"block"],
			
			"quartzites_a" 	=> ["name" => "Йuartzites_a", 	"hardness" => 1.5, "gravity" => false,	"voxel"=>"cuber", "placement"=>"block"],
			"quartzites_b" 	=> ["name" => "Йuartzites_b", 	"hardness" => 1.5, "gravity" => false,	"voxel"=>"cuber", "placement"=>"block"],
			"quartzites_c" 	=> ["name" => "Йuartzites_c", 	"hardness" => 1.5, "gravity" => false,	"voxel"=>"cuber", "placement"=>"block"],
			"quartzites_d" 	=> ["name" => "Йuartzites_d", 	"hardness" => 1.5, "gravity" => false,	"voxel"=>"cuber", "placement"=>"block"],
			"quartzites_e" 	=> ["name" => "Йuartzites_e", 	"hardness" => 1.5, "gravity" => false,	"voxel"=>"cuber", "placement"=>"block"],
			"quartzites_f" 	=> ["name" => "Йuartzites_f", 	"hardness" => 1.5, "gravity" => false,	"voxel"=>"cuber", "placement"=>"block"],
			
			"shales_a" 		=> ["name" => "Shales_a", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered", "placement"=>"block"],
			"shales_b" 		=> ["name" => "Shales_b", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered", "placement"=>"block"],
			"shales_c" 		=> ["name" => "Shales_c", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered", "placement"=>"block"],
			"shales_d" 		=> ["name" => "Shales_d", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered", "placement"=>"block"],
			"shales_e" 		=> ["name" => "Shales_e", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"layered", "placement"=>"block"],
			
			"tuff_a" 		=> ["name" => "Tuff_a", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cubev", "placement"=>"block"],
			"tuff_b" 		=> ["name" => "Tuff_b", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cubev", "placement"=>"block"],
			"tuff_c" 		=> ["name" => "Tuff_c", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cubev", "placement"=>"block"],
			"tuff_d" 		=> ["name" => "Tuff_d", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cubev", "placement"=>"block"],
			"tuff_e" 		=> ["name" => "Tuff_e", 		"hardness" => 0.3, "gravity" => false,	"voxel"=>"cubev", "placement"=>"block"],
			
			"ferrum" 		=> ["name" => "Ferrum", 		"hardness" => 1.5, "gravity" => false,	"voxel"=>"cubev"] */
		];
		
		$resourceMaterials=[
			"stone_ferrum"=>[
				"base"=>["key"=>"stone","name"=>"Stone","hardness"=>1.5,"gravity"=>false],
				"res"=>["key"=>"ferrum","name"=>"Ferrum"],"placement"=>"block"
			]/* ,
			"stone_cuprum"=>[
				"base"=>["key"=>"stone","name"=>"Stone","hardness"=>1.5,"gravity"=>false],
				"resource"=>["key"=>"cuprum","name"=>"Cuprum"],"placement"=>"block"
			] */
		];
		
		$treeMaterials=[
			"oak"	=>	["name"	=>	"Oak",	"hardness"	=>	1.5,	"gravity"	=>	false,	"stripped"	=>	true]
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
		
		// Регистрация ресурсных воксельных блоков.
		ResourceBlockRegistrar::registerFamily(
			"res",
			$resourceMaterials,
			ResourceVoxelCatalog::getVariants(),
			$progress,
			10
		);
		
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