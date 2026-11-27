<?php

declare(strict_types=1);

namespace pluginfd\material;

use customiesdevs\customies\block\properties\Material;
use customiesdevs\customies\block\properties\RenderMethod;
use customiesdevs\customies\block\properties\TintMethod;

final class BlockMaterialCatalog{

    /* @return Material[] */
    public static function create(string $material): array{
        $material=strtolower(trim($material));

        if($material==="grass"){
            return [
                new Material(
                    Material::TARGET_ALL,
                    "pluginfd:".$material,
                    RenderMethod::ALPHA_TEST_SINGLE_SIDED_TO_OPAQUE,
                    TintMethod::GRASS,
                    1.0,
                    true,
                    false,
                    true
                )
            ];
        }

        return [
            new Material(
                Material::TARGET_ALL,
                "pluginfd:".$material,
                RenderMethod::OPAQUE
            )
        ];
    }
}