<?php

declare(strict_types=1);

namespace pluginfd\block;

use customiesdevs\customies\block\component\BlockComponents;
use customiesdevs\customies\block\component\BlockComponentsTrait;
use customiesdevs\customies\block\component\GeometryComponent;
use customiesdevs\customies\block\component\MaterialInstancesComponent;
use customiesdevs\customies\block\component\CollisionBoxComponent;
use customiesdevs\customies\block\properties\Material;
use customiesdevs\customies\block\states\BlockState;

use pluginfd\variant\BoneGroupProcessor;

use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;

use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\data\runtime\RuntimeDataDescriber;

use InvalidArgumentException;

final class ConfiguredTreeBlock extends Block implements BlockComponents{

    use BlockComponentsTrait;

    private bool $gravity;

    /** @var array<string, array<string, mixed>> */
    private array $stateDefinitions=[];

    /** @var array<string, int|bool> */
    private array $stateValues=[];

    /** @var array<string, string> */
    private array $boneVisibility=[];

    /** @var array<string, array<string, mixed>> */
    private array $debugActions=[];

    /** @param Material[] $materials */
    public function __construct(
        BlockIdentifier $identifier,
        array $variant,
        array $materials,
        float $hardness,
        bool $gravity
    ){
        $geometry=$variant["geometry"] ?? null;
        $groups=$variant["bone_groups"] ?? null;

        if(!is_string($geometry) || $geometry === ""){
            throw new InvalidArgumentException("Tree variant has no geometry");
        }

        if(!is_array($groups)){
            throw new InvalidArgumentException("Tree variant has no bone_groups");
        }

        $processed=BoneGroupProcessor::process($groups);

        $this->stateDefinitions=$processed["states"];
        $this->boneVisibility=$processed["visibility"];
        $this->debugActions=$processed["debug"];

        foreach($this->stateDefinitions as $name=>$definition){
            $this->stateValues[$name]=$definition["default"];
        }

        parent::__construct(
            $identifier,
            $variant["name"],
            new BlockTypeInfo(new BlockBreakInfo($hardness))
        );

        $this->gravity=$gravity;

        $this->registerStates();
        $this->initComponents($geometry,$materials);
    }

    private function registerStates(): void{
        foreach($this->stateDefinitions as $name=>$definition){
            $type=$definition["type"];

            if($type === "int"){
                $this->addState(
                    new BlockState(
                        "pluginfd:".$name,
                        range($definition["min"],$definition["max"])
                    )
                );
            }elseif($type === "bool"){
                $this->addState(
                    new BlockState(
                        "pluginfd:".$name,
                        [false,true]
                    )
                );
            }else{
                throw new InvalidArgumentException(
                    "Unsupported state type '$type'"
                );
            }
        }
    }

    public function hasGravity(): bool{
        return $this->gravity;
    }

    /**
     * @param Material[] $materials
     */
    private function initComponents(
        string $geometry,
        array $materials
    ): void{
        $this->addComponent(
            new GeometryComponent(
                $geometry,
                $this->boneVisibility
            )
        );

        $this->addComponent(
            new MaterialInstancesComponent($materials)
        );

        $this->addComponent(
            new CollisionBoxComponent(false)
        );
    }

    public function getCurrentStates(): array{
        return array_values($this->stateValues);
    }

    public function getStateValue(string $name): int|bool{
        if(!array_key_exists($name,$this->stateValues)){
            throw new InvalidArgumentException(
                "Unknown tree block state '$name'"
            );
        }

        return $this->stateValues[$name];
    }

    public function setState(string $name,int|bool $value): self{
        if(!isset($this->stateDefinitions[$name])){
            throw new InvalidArgumentException(
                "Unknown tree block state '$name'"
            );
        }

        $definition=$this->stateDefinitions[$name];

        if($definition["type"] === "int"){
            if(!is_int($value)){
                throw new InvalidArgumentException(
                    "State '$name' must be an integer"
                );
            }

            if($value < $definition["min"] || $value > $definition["max"]){
                throw new InvalidArgumentException(
                    "State '$name' must be between ".
                    $definition["min"]." and ".$definition["max"]
                );
            }
        }elseif($definition["type"] === "bool"){
            if(!is_bool($value)){
                throw new InvalidArgumentException(
                    "State '$name' must be a boolean"
                );
            }
        }

        $this->stateValues[$name]=$value;

        return $this;
    }

    public function serializeState(BlockStateWriter $out): void{
        foreach($this->stateDefinitions as $name=>$definition){
            $stateName="pluginfd:".$name;
            $value=$this->stateValues[$name];

            if($definition["type"] === "int"){
                $out->writeInt($stateName,$value);
            }else{
                $out->writeBool($stateName,$value);
            }
        }
    }

    public function deserializeState(BlockStateReader $in): void{
        foreach($this->stateDefinitions as $name=>$definition){
            $stateName="pluginfd:".$name;

            if($definition["type"] === "int"){
                $this->stateValues[$name]=$in->readBoundedInt(
                    $stateName,
                    $definition["min"],
                    $definition["max"]
                );
            }else{
                $this->stateValues[$name]=$in->readBool($stateName);
            }
        }
    }

    protected function describeBlockOnlyState(RuntimeDataDescriber $w): void{
        foreach($this->stateDefinitions as $name=>$definition){
            $value=&$this->stateValues[$name];

            if($definition["type"] === "bool"){
                $w->bool($value);
            }elseif($definition["type"] === "int"){
                $w->boundedIntAuto(
                    $definition["min"],
                    $definition["max"],
                    $value
                );
            }else{
                throw new InvalidArgumentException(
                    "Unsupported state type '{$definition["type"]}'"
                );
            }

            unset($value);
        }
    }

    public function getDebugActions(): array{
        return $this->debugActions;
    }
}