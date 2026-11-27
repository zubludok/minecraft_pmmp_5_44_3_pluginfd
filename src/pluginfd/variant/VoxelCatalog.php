<?php

declare(strict_types=1);

namespace pluginfd\variant;

final class VoxelCatalog{

    private const PROFILES=[
        "cube"=>[
            "geometry"=>"geometry.voxel.cube",
            "bones"=>[]
        ],
        "cubev"=>[
            "geometry"=>"geometry.voxel.cubev",
            "bones"=>["ba","bb","bc","bd","be","bf","bg","bh"]
        ],
		"cuber"=>[
			"geometry"=>"geometry.voxel.cuber",
			"bones"=>["ba","bb","bc","bd","be","bf","bg","bh"]
		],
        "layered"=>[
            "geometry"=>"geometry.voxel.layered",
            "bones"=>["ba","bb","bc","bd"]
        ],
        "layered_half"=>[
            "geometry"=>"geometry.voxel.layered_half",
            "bones"=>["ba","bb"]
        ],
        "columnar"=>[
            "geometry"=>"geometry.voxel.columnar",
            "bones"=>["ba","bb","bc","bd"]
        ]
    ];

    public static function getProfile(string $type): array{
        if(!isset(self::PROFILES[$type])){
            throw new \InvalidArgumentException("Unknown voxel type: ".$type);
        }
        return self::PROFILES[$type];
    }

    public static function getTypes(): array{
        return array_keys(self::PROFILES);
    }
}