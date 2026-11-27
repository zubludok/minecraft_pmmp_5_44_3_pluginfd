<?php

declare(strict_types=1);

namespace pluginfd\variant;

final class ResourceVoxelCatalog{

    public static function getVariants(): array{
        return [
            [
                "suffix" => "si",
                "size" => 0.2,
                "style" => "inclusions",
                "geometry" => "geometry.voxel_res.small"
            ],
            [
                "suffix" => "mi",
                "size" => 0.4,
                "style" => "inclusions",
                "geometry" => "geometry.voxel_res.medium"
            ],
            [
                "suffix" => "li",
                "size" => 0.6,
                "style" => "inclusions",
                "geometry" => "geometry.voxel_res.large"
            ],
            [
                "suffix" => "sn",
                "size" => 0.2,
                "style" => "nugget",
                "geometry" => "geometry.voxel_res.small"
            ],
            [
                "suffix" => "mn",
                "size" => 0.4,
                "style" => "nugget",
                "geometry" => "geometry.voxel_res.medium"
            ],
            [
                "suffix" => "ln",
                "size" => 0.6,
                "style" => "nugget",
                "geometry" => "geometry.voxel_res.large"
            ]
        ];
    }
}