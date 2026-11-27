<?php

declare(strict_types=1);

namespace pluginfd\block;

use customiesdevs\customies\block\component\BlockComponents;
use customiesdevs\customies\block\component\BlockComponentsTrait;
use customiesdevs\customies\block\component\GeometryComponent;
use customiesdevs\customies\block\component\MaterialInstancesComponent;
use customiesdevs\customies\block\component\CollisionBoxComponent;

use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;

final class VoxelPlaceholderBlock extends Block implements BlockComponents{

    use BlockComponentsTrait;

    private static array $targets=[];

    public function __construct(
        BlockIdentifier $identifier,
        string $name,
        array $materials,
        float $hardness
    ){
        parent::__construct(
            $identifier,
            $name,
            new BlockTypeInfo(new BlockBreakInfo($hardness))
        );

        $this->addComponent(new GeometryComponent("minecraft:geometry.full_block"));
        $this->addComponent(new MaterialInstancesComponent($materials));
        $this->addComponent(new CollisionBoxComponent(true));
    }

    public static function bind(int $blockId,string $targetIdentifier): void{
        self::$targets[$blockId]=$targetIdentifier;
    }

    public static function getTargetIdentifier(int $itemTypeId): ?string{
        return self::$targets[$itemTypeId]??null;
    }
}