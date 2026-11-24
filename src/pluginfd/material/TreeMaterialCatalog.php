<?php

declare(strict_types=1);

namespace pluginfd\material;

use customiesdevs\customies\block\properties\Material;
use customiesdevs\customies\block\properties\RenderMethod;

final class TreeMaterialCatalog{

    public static function create(string $materialKey): array{

        return [
            new Material(Material::TARGET_ALL,"pluginfd:tree_" . $materialKey,RenderMethod::ALPHA_TEST_SINGLE_SIDED_TO_OPAQUE)
        ];
    }
}