<?php

declare(strict_types=1);

namespace pluginfd\material;

use customiesdevs\customies\block\properties\Material;
use customiesdevs\customies\block\properties\RenderMethod;

final class ResourceMaterialCatalog{

    /* @return Material[] */
    public static function create(
        string $baseMaterial,
        string $resourceMaterial,
        string $style
    ): array{
        $baseTexture = "pluginfd:" . $baseMaterial;
        $resourceSuffix = match($style){
            "inclusions" => "i",
            "nugget" => "n",
            default => throw new \InvalidArgumentException("Unknown resource style: " . $style)
        };
        $resourceTexture = "pluginfd:res_" . $resourceMaterial . "_" . $resourceSuffix;
        if($style === "inclusions"){
            return [
                new Material(Material::TARGET_ALL,$baseTexture,RenderMethod::ALPHA_TEST_SINGLE_SIDED_TO_OPAQUE),
                new Material("res",$resourceTexture,RenderMethod::ALPHA_TEST_SINGLE_SIDED_TO_OPAQUE)
            ];
        }
        return [
            new Material(Material::TARGET_ALL,$baseTexture,RenderMethod::OPAQUE),
            new Material("res",$resourceTexture,RenderMethod::OPAQUE)
        ];
    }
}