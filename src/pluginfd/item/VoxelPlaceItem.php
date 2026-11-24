<?php

declare(strict_types=1);

namespace pluginfd\item;

use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use customiesdevs\customies\item\component\IconComponent;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;

final class VoxelPlaceItem extends Item implements ItemComponents{
    use ItemComponentsTrait;

    public function __construct(
        ItemIdentifier $identifier,
        string $name,
        string $icon,
        private string $blockIdentifier
    ){
        parent::__construct($identifier, $name);

        $this->addComponent(new IconComponent($icon));
    }

    public function getBlockIdentifier(): string{
        return $this->blockIdentifier;
    }
}