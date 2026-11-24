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
use customiesdevs\customies\block\permutations\BlockPermutation;
use customiesdevs\customies\block\permutations\BlockPermutations;
use customiesdevs\customies\block\permutations\BlockPermutationsTrait;

use pluginfd\variant\BoneGroupProcessor;

use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;

use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\data\runtime\RuntimeDataDescriber;

use InvalidArgumentException;

final class ConfiguredTreeBlock extends Block implements BlockComponents, BlockPermutations{

    use BlockComponentsTrait;
    use BlockPermutationsTrait;

    private bool $gravity;

    /** @var array<string, array<string, mixed>> */
    private array $stateDefinitions = [];

    /** @var array<string, int|bool> */
    private array $stateValues = [];

    /** @var array<string, string> */
    private array $boneVisibility = [];
	
	/** @var array<string, array<string, mixed>> */
	private array $debugActions = [];
	
    /**
     * @param Material[] $materials
     */
	public function __construct(
		BlockIdentifier $identifier,
		array $variant,
		array $materials,
		float $hardness,
		bool $gravity
	){
		$geometry = $variant["geometry"] ?? null;
		$groups = $variant["bone_groups"] ?? null;

		if(!is_string($geometry) || $geometry === ""){
			throw new InvalidArgumentException("Tree variant has no geometry");
		}

		if(!is_array($groups)){
			throw new InvalidArgumentException("Tree variant has no bone_groups");
		}

		// 1. Подготавливаем конфигурацию костей.
		$processed = BoneGroupProcessor::process($groups);
		
		$this->stateDefinitions = $processed["states"];
		$this->boneVisibility = $processed["visibility"];
		$this->debugActions = $processed["debug"];

		// 2. Подготавливаем начальные значения состояний.
		foreach($this->stateDefinitions as $name => $definition){
			$this->stateValues[$name] = $definition["default"];
		}

		// 3. Теперь PocketMine сможет правильно определить размер Runtime ID.
		parent::__construct(
			$identifier,
			$variant["name"],
			new BlockTypeInfo(new BlockBreakInfo($hardness))
		);

		$this->gravity = $gravity;

		// 4. Регистрируем состояния Customies.
		$this->registerStates();

		// 5. Подключаем компоненты.
		$this->initComponents($geometry, $materials);
	}
	
	private function registerStates(): void{
		foreach($this->stateDefinitions as $name => $definition){

			$type = $definition["type"];

			if($type === "int"){
				$min = $definition["min"];
				$max = $definition["max"];

				$this->addState(
					new BlockState(
						"pluginfd:" . $name,
						range($min, $max)
					)
				);

			}elseif($type === "bool"){

				$this->addState(
					new BlockState(
						"pluginfd:" . $name,
						[false, true]
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
     * Подключаем геометрию, материалы и видимость костей.
     *
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

        /*
         * Временная настройка.
         * Клиентскую коллизию подключим отдельно,
         * когда определим конфигурацию collision для костей.
         */
        $this->addComponent(
            new CollisionBoxComponent(false)
        );
    }

    /**
     * Состояния в том же порядке, в котором они зарегистрированы.
     */
    public function getCurrentStates(): array{
        return array_values($this->stateValues);
    }

    /**
     * Получить значение состояния.
     */
	public function getStateValue(string $name): int|bool{
		if(!array_key_exists($name, $this->stateValues)){
			throw new InvalidArgumentException(
				"Unknown tree block state '$name'"
			);
		}

		return $this->stateValues[$name];
	}

    /**
     * Изменить состояние.
     */
    public function setState(string $name, int|bool $value): self{
        if(!isset($this->stateDefinitions[$name])){
            throw new InvalidArgumentException(
                "Unknown tree block state '$name'"
            );
        }

        $definition = $this->stateDefinitions[$name];

        if($definition["type"] === "int"){
            if(!is_int($value)){
                throw new InvalidArgumentException(
                    "State '$name' must be an integer"
                );
            }

            if($value < $definition["min"] || $value > $definition["max"]){
                throw new InvalidArgumentException(
                    "State '$name' must be between " .
                    $definition["min"] . " and " . $definition["max"]
                );
            }

        }elseif($definition["type"] === "bool"){
            if(!is_bool($value)){
                throw new InvalidArgumentException(
                    "State '$name' must be a boolean"
                );
            }
        }

        $this->stateValues[$name] = $value;

        return $this;
    }

    /**
     * Сериализация состояний для сохранения блока.
     */
    public function serializeState(BlockStateWriter $out): void{
        foreach($this->stateDefinitions as $name => $definition){

            $value = $this->stateValues[$name];
            $stateName = "pluginfd:" . $name;

            if($definition["type"] === "int"){
                $out->writeInt($stateName, $value);
            }else{
                $out->writeBool($stateName, $value);
            }
        }
    }

    /**
     * Десериализация состояний.
     */
    public function deserializeState(BlockStateReader $in): void{
        foreach($this->stateDefinitions as $name => $definition){

            $stateName = "pluginfd:" . $name;

            if($definition["type"] === "int"){
                $this->stateValues[$name] = $in->readBoundedInt(
                    $stateName,
                    $definition["min"],
                    $definition["max"]
                );
            }else{
                $this->stateValues[$name] = $in->readBool($stateName);
            }
        }
    }

    /**
     * Описание состояний для Runtime ID.
     */
	protected function describeBlockOnlyState(RuntimeDataDescriber $w): void{
		foreach($this->stateDefinitions as $name => $definition){

			// Получаем непосредственную ссылку на значение состояния.
			$value = &$this->stateValues[$name];

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

			// Разрываем ссылку перед следующей итерацией.
			unset($value);
		}
	}
	
	/**
	 * Получить отладочные действия, настроенные для дерева.
	 */
	 
	public function getDebugActions(): array{
		return $this->debugActions;
	}
}