<?php

declare(strict_types=1);

namespace pluginfd\block;

use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;

class TrashBlock extends Block{

    public function __construct(BlockIdentifier $identifier){
        parent::__construct(
            $identifier,
            "trash_block",
            new BlockTypeInfo(
                new BlockBreakInfo(3.0) // Чуть мягче чем рубиновый блок
            )
        );
    }
}