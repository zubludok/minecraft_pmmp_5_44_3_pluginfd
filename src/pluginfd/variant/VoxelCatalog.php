<?php

declare(strict_types=1);

namespace pluginfd\variant;

final class VoxelCatalog{

    /* @return BlockVariantDefinition[] */
    public static function getVariants(): array{
        $variants = [];
        $types = [
            "cubev"
        ];
		foreach($types as $type){
			$suffix = "{$type}";
			$geometry = "geometry.voxel.{$type}";
			$variants[] = new BlockVariantDefinition($suffix,$geometry);
		}
        return $variants;
    }
}