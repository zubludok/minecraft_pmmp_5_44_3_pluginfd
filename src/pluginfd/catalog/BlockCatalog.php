<?php

declare(strict_types=1);

namespace pluginfd\catalog;

final class BlockCatalog{

    private static array $blocks=[];

    private const GEOLOGY_ALIASES=[
        "stone"=>"STONE",
        "granit"=>"GRANITE",
        "granite"=>"GRANITE",
        "diorite"=>"DIORITE",
        "andesite"=>"ANDESITE",
        "basalt"=>"BASALT",
        "tuff"=>"TUFF",
        "deepslate"=>"DEEPSLATE",
        "calcite"=>"CALCITE",
        "sand"=>"SAND",
        "sand_white"=>"SAND",
        "sand_beige"=>"SAND",
        "sand_orange"=>"SAND",
        "gravel"=>"GRAVEL",
        "clay"=>"CLAY",
        "limestone"=>"LIMESTONE",
        "dripstone"=>"DRIPSTONE",
        "sandstone"=>"SANDSTONE",
        "red_sandstone"=>"RED_SANDSTONE",
        "marble"=>"MARBLE",
        "shale"=>"SHALE",
        "shales"=>"SHALE",
        "gneiss"=>"GNEISS",
        "quartzite"=>"QUARTZITE",
        "quartzites"=>"QUARTZITE"
    ];

    public static function register(string $identifier,array $data): void{
        $identifier=strtolower(trim($identifier));
        if($identifier===""){
            throw new \InvalidArgumentException("Block identifier cannot be empty");
        }
        if(isset(self::$blocks[$identifier])){
            throw new \InvalidArgumentException("Block already exists in catalog: ".$identifier);
        }
        $material=strtolower(trim((string)($data["material"]??"")));
        $geology=self::resolveGeology(
            $data["geology"]??null,
            $material
        );
        self::$blocks[$identifier]=[
            "identifier"=>$identifier,
            "family"=>(string)($data["family"]??""),
            "material"=>$material,
            "role"=>(string)($data["role"]??($geology!==null?"rock":"block")),
            "geology"=>$geology,
            "voxel"=>(string)($data["voxel"]??""),
            "geometry"=>(string)($data["geometry"]??""),
            "base_material"=>$data["base_material"]??null,
            "resource_material"=>$data["resource_material"]??null,
            "variant"=>$data["variant"]??null,
            "stripped"=>(bool)($data["stripped"]??false)
        ];
    }

    private static function resolveGeology(
        mixed $geology,
        string $material
    ): ?string{
        if(is_string($geology) && trim($geology)!==""){
            return strtoupper(trim($geology));
        }
        // Убираем буквенный суффикс варианта: granit_a -> granit.
        $base=preg_replace('/_[a-z]$/i',"",$material)??$material;
        return self::GEOLOGY_ALIASES[$base]??null;
    }

    public static function getAll(): array{
        return array_values(self::$blocks);
    }

    public static function getByIdentifier(string $identifier): ?array{
        return self::$blocks[strtolower(trim($identifier))]??null;
    }

    public static function getGeologyCatalog(): array{
        $catalog=[];
        foreach(self::$blocks as $block){
            if($block["role"]!=="rock" || $block["geology"]===null){
                continue;
            }
            $catalog[$block["geology"]][]=$block;
        }
        return $catalog;
    }

    public static function getGeologyVariants(string $geology): array{
        $geology=strtoupper(trim($geology));
        return self::getGeologyCatalog()[$geology]??[];
    }

    public static function export(): array{
        return [
            "version"=>1,
            "blocks"=>self::getAll(),
            "geology"=>self::getGeologyCatalog()
        ];
    }

    public static function getCount(): int{
        return count(self::$blocks);
    }
}