<?php

declare(strict_types=1);

namespace pluginfd\material;

use customiesdevs\customies\block\properties\Material;
use customiesdevs\customies\block\properties\RenderMethod;
use customiesdevs\customies\block\properties\TintMethod;

final class BlockMaterialCatalog{

    /* @return Material[] */
    public static function create(string $material,string $variant): array{
        switch($material){
			
			case "grass": return [new Material(Material::TARGET_ALL,"pluginfd:grass",RenderMethod::ALPHA_TEST_SINGLE_SIDED_TO_OPAQUE,TintMethod::GRASS,1.0,true,false,true)];
				
            case "sand_white": return [new Material(Material::TARGET_ALL,"pluginfd:sand_white",RenderMethod::OPAQUE)];
            case "sand_beige": return [new Material(Material::TARGET_ALL,"pluginfd:sand_beige",RenderMethod::OPAQUE)];
            case "sand_orange": return [new Material(Material::TARGET_ALL,"pluginfd:sand_orange",RenderMethod::OPAQUE)];

            case "gravel": return [new Material(Material::TARGET_ALL,"pluginfd:gravel",RenderMethod::OPAQUE)];
				
            case "granit_a": return [new Material(Material::TARGET_ALL,"pluginfd:granit_a",RenderMethod::OPAQUE)];
            case "granit_b": return [new Material(Material::TARGET_ALL,"pluginfd:granit_b",RenderMethod::OPAQUE)];
            case "granit_c": return [new Material(Material::TARGET_ALL,"pluginfd:granit_c",RenderMethod::OPAQUE)];
            case "granit_d": return [new Material(Material::TARGET_ALL,"pluginfd:granit_d",RenderMethod::OPAQUE)];
            case "granit_e": return [new Material(Material::TARGET_ALL,"pluginfd:granit_e",RenderMethod::OPAQUE)];
            case "granit_f": return [new Material(Material::TARGET_ALL,"pluginfd:granit_f",RenderMethod::OPAQUE)];
            case "granit_g": return [new Material(Material::TARGET_ALL,"pluginfd:granit_g",RenderMethod::OPAQUE)];
            case "granit_h": return [new Material(Material::TARGET_ALL,"pluginfd:granit_h",RenderMethod::OPAQUE)];
            case "granit_i": return [new Material(Material::TARGET_ALL,"pluginfd:granit_i",RenderMethod::OPAQUE)];
            case "granit_j": return [new Material(Material::TARGET_ALL,"pluginfd:granit_j",RenderMethod::OPAQUE)];
            case "granit_k": return [new Material(Material::TARGET_ALL,"pluginfd:granit_k",RenderMethod::OPAQUE)];
			
            case "calcite_a": return [new Material(Material::TARGET_ALL,"pluginfd:calcite_a",RenderMethod::OPAQUE)];
            case "calcite_b": return [new Material(Material::TARGET_ALL,"pluginfd:calcite_b",RenderMethod::OPAQUE)];
            case "calcite_c": return [new Material(Material::TARGET_ALL,"pluginfd:calcite_c",RenderMethod::OPAQUE)];
            case "calcite_d": return [new Material(Material::TARGET_ALL,"pluginfd:calcite_d",RenderMethod::OPAQUE)];
            case "calcite_e": return [new Material(Material::TARGET_ALL,"pluginfd:calcite_e",RenderMethod::OPAQUE)];
            case "calcite_f": return [new Material(Material::TARGET_ALL,"pluginfd:calcite_f",RenderMethod::OPAQUE)];
			
            case "basalt_a": return [new Material(Material::TARGET_ALL,"pluginfd:basalt_a",RenderMethod::OPAQUE)];
            case "basalt_b": return [new Material(Material::TARGET_ALL,"pluginfd:basalt_b",RenderMethod::OPAQUE)];
            case "basalt_c": return [new Material(Material::TARGET_ALL,"pluginfd:basalt_c",RenderMethod::OPAQUE)];
			
            case "tuff_a": return [new Material(Material::TARGET_ALL,"pluginfd:tuff_a",RenderMethod::OPAQUE)];
            case "tuff_b": return [new Material(Material::TARGET_ALL,"pluginfd:tuff_b",RenderMethod::OPAQUE)];
            case "tuff_c": return [new Material(Material::TARGET_ALL,"pluginfd:tuff_c",RenderMethod::OPAQUE)];
            case "tuff_d": return [new Material(Material::TARGET_ALL,"pluginfd:tuff_d",RenderMethod::OPAQUE)];
            case "tuff_e": return [new Material(Material::TARGET_ALL,"pluginfd:tuff_e",RenderMethod::OPAQUE)];
				
            case "dirt": return [new Material(Material::TARGET_ALL,"pluginfd:dirt",RenderMethod::OPAQUE)];

            case "stone": return [new Material(Material::TARGET_ALL,"pluginfd:stone",RenderMethod::OPAQUE)];
			
		}

        throw new \InvalidArgumentException(
            "Unknown block material: " . $material
        );
    }
}