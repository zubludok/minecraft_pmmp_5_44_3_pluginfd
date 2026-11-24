<?php

declare(strict_types=1);

namespace pluginfd\variant;

final class ResourceVoxelCatalog{

    public static function getVariants(): array{
        return [
            [
                "suffix" => "small_inclusions",
                "size" => 0.2,
                "style" => "inclusions",
                "geometry" => "geometry.voxel_resource.small"
            ],
            [
                "suffix" => "medium_inclusions",
                "size" => 0.4,
                "style" => "inclusions",
                "geometry" => "geometry.voxel_resource.medium"
            ],
            [
                "suffix" => "large_inclusions",
                "size" => 0.6,
                "style" => "inclusions",
                "geometry" => "geometry.voxel_resource.large"
            ],
            [
                "suffix" => "small_nugget",
                "size" => 0.2,
                "style" => "nugget",
                "geometry" => "geometry.voxel_resource.small"
            ],
            [
                "suffix" => "medium_nugget",
                "size" => 0.4,
                "style" => "nugget",
                "geometry" => "geometry.voxel_resource.medium"
            ],
            [
                "suffix" => "large_nugget",
                "size" => 0.6,
                "style" => "nugget",
                "geometry" => "geometry.voxel_resource.large"
            ]
        ];
    }
}