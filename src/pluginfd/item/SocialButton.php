<?php

declare(strict_types=1);

namespace pluginfd\item;

use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use customiesdevs\customies\item\component\DisplayNameComponent;
use customiesdevs\customies\item\component\IconComponent;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;

final class SocialButton extends Item implements ItemComponents{

    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier){
        $this->components = [];

        parent::__construct($identifier, "social_button");

        $this->addComponent(new IconComponent("social_button"));
        $this->addComponent(
            new DisplayNameComponent("item.pluginfd:social_button.name")
        );
    }
}