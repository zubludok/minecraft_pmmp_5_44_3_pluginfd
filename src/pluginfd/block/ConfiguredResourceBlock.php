<?php

declare(strict_types=1);

namespace pluginfd\block;

use customiesdevs\customies\block\component\BlockComponents;
use customiesdevs\customies\block\component\BlockComponentsTrait;
use customiesdevs\customies\block\component\GeometryComponent;
use customiesdevs\customies\block\component\MaterialInstancesComponent;
use customiesdevs\customies\block\component\CollisionBoxComponent;
use customiesdevs\customies\block\component\LiquidDetectionComponent;
use customiesdevs\customies\block\permutations\BlockPermutations;
use customiesdevs\customies\block\permutations\BlockPermutationsTrait;
use customiesdevs\customies\block\permutations\BlockPermutation;
use customiesdevs\customies\block\properties\Material;
use customiesdevs\customies\block\properties\Box;
use customiesdevs\customies\block\states\BlockState;

use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;

use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\data\runtime\RuntimeDataDescriber;

use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

final class ConfiguredResourceBlock extends Block implements BlockComponents, BlockPermutations{

    use BlockComponentsTrait;
    use BlockPermutationsTrait;

    public const STATE_MASK = "pluginfd:voxel_mask";
    public const STATE_RESOURCE = "pluginfd:resource_visible";

    private int $mask = 255;
    private bool $resourceVisible = true;

    private bool $gravity = false;

    /** Размер залежи относительно блока. */
    private float $resourceSize;

    /** @var array<int, string> */
    private const BONES = [
        0 => "vfta",
        1 => "vftb",
        2 => "vftc",
        3 => "vftd",
        4 => "vfba",
        5 => "vfbb",
        6 => "vfbc",
        7 => "vfbd"
    ];
	
	private const RESOURCE_BONE = "res";
	
    private string $baseMaterialKey;
	
	/**
     * @param Material[] $materials
     */
    public function __construct(
        BlockIdentifier $identifier,
        string $name,
        string $geometry,
        array $materials,
        float $hardness,
        bool $gravity,
        float $resourceSize,
		string $baseMaterialKey
    ){
        if(!in_array($resourceSize, [0.2, 0.4, 0.6], true)){
            throw new \InvalidArgumentException("Resource size must be 0.2, 0.4 or 0.6");
        }
        parent::__construct(
            $identifier,
            $name,
            new BlockTypeInfo(new BlockBreakInfo($hardness))
        );
		$this->gravity = $gravity;
		$this->resourceSize = $resourceSize;
		$this->baseMaterialKey = $baseMaterialKey;
		$this->initStates();
		$this->initComponents($geometry, $materials);
		$this->initPermutations();
    }

    protected function describeBlockOnlyState(RuntimeDataDescriber $w): void{
        $w->int(8, $this->mask);
        $w->bool($this->resourceVisible);
    }

    public function hasGravity(): bool{
        return $this->gravity;
    }

    public function getResourceSize(): float{
        return $this->resourceSize;
    }

	public function getBaseMaterialKey(): string{
		return $this->baseMaterialKey;
	}
	
    public function hasResource(): bool{
        return $this->resourceVisible;
    }

    public function setResourceVisible(bool $visible): self{
        $this->resourceVisible = $visible;
        return $this;
    }

    private function initStates(): void{
        $this->addState(new BlockState(self::STATE_MASK, range(0, 255)));
        $this->addState(new BlockState(self::STATE_RESOURCE, [false, true]));
    }

    /**
     * @param Material[] $materials
     */
	private function initComponents(string $geometry, array $materials): void{
		$boneVisibility = [];
		// Восемь обычных воксельных костей.
		for($bit = 0; $bit < 8; ++$bit){
			$bone = self::BONES[$bit];
			$divisor = 1 << $bit;
			$boneVisibility[$bone] = "math.mod(math.floor(q.block_state('".self::STATE_MASK."') / ".$divisor."), 2) == 1";
		}
		// Отдельная центральная кость залежи.
		$boneVisibility[self::RESOURCE_BONE] = "q.block_state('" . self::STATE_RESOURCE . "') == true";
		$this->addComponent(new GeometryComponent($geometry, $boneVisibility));
		$this->addComponent(new MaterialInstancesComponent($materials));
		$this->addComponent(new CollisionBoxComponent(false));
	}

    public function getCurrentStates(): array{
        return [$this->mask,$this->resourceVisible];
    }

    public function serializeState(BlockStateWriter $out): void{
        $out->writeInt(self::STATE_MASK, $this->mask);
        $out->writeBool(self::STATE_RESOURCE, $this->resourceVisible);
    }

    public function deserializeState(BlockStateReader $in): void{
        $this->mask = $in->readBoundedInt(self::STATE_MASK, 0, 255);
        $this->resourceVisible = $in->readBool(self::STATE_RESOURCE);
        $this->collisionBoxes = null;
    }

    public function getMask(): int{
        return $this->mask;
    }

    public function isBoneVisible(int $bit): bool{
        if($bit < 0 || $bit > 7){
            throw new \InvalidArgumentException("Voxel bit must be between 0 and 7");
        }
        return ($this->mask & (1 << $bit)) !== 0;
    }

    public function setMask(int $mask): self{
        if($mask < 0 || $mask > 255){
            throw new \InvalidArgumentException("Voxel mask must be between 0 and 255");
        }
        $this->mask = $mask;
        $this->collisionBoxes = null;
        return $this;
    }

    protected function recalculateCollisionBoxes(): array{
        $boxes = [];
        for($bit = 0; $bit < 8; ++$bit){
            if(($this->mask & (1 << $bit)) === 0){
                continue;
            }
            $upper = $bit < 4;
            $corner = $bit % 4;
            $east = $corner === 2 || $corner === 3;
            $south = $corner === 1 || $corner === 3;
            $minX = $east ? 0.5 : 0.0;
            $maxX = $east ? 1.0 : 0.5;
            $minY = $upper ? 0.5 : 0.0;
            $maxY = $upper ? 1.0 : 0.5;
            $minZ = $south ? 0.5 : 0.0;
            $maxZ = $south ? 1.0 : 0.5;
            $boxes[] = new AxisAlignedBB($minX,$minY,$minZ,$maxX,$maxY,$maxZ);
        }
        return $boxes;
    }

	private function initPermutations(): void{
		for($mask = 0; $mask <= 255; ++$mask){
			$collision = new CollisionBoxComponent($mask !== 0);
			if($mask !== 0){
				$boxes = [];
				for($bit = 0; $bit < 8; ++$bit){
					if(($mask & (1 << $bit)) === 0){
						continue;
					}
					$upper = $bit < 4;
					$corner = $bit % 4;
					$east = $corner === 2 || $corner === 3;
					$south = $corner === 1 || $corner === 3;
					$minX = $east ? 0 : -8;
					$maxX = $east ? 8 : 0;
					$minY = $upper ? 8 : 0;
					$maxY = $upper ? 16 : 8;
					$minZ = $south ? 0 : -8;
					$maxZ = $south ? 8 : 0;
					$boxes[] = new Box(
						new Vector3($minX, $minY, $minZ),
						new Vector3($maxX - $minX,$maxY - $minY,$maxZ - $minZ)
					);
				}
				$collision->addBoxes($boxes);
			}
			$condition = "q.block_state('" . self::STATE_MASK . "') == " . $mask;
			$liquid = new LiquidDetectionComponent(
				true,
				LiquidDetectionComponent::LIQUID_WATER,
				$mask === 255 ? LiquidDetectionComponent::BLOCKING : LiquidDetectionComponent::NO_REACTION
			);

			$this->addPermutation(new BlockPermutation($condition,[$collision, $liquid]));
		}
	}
	
	// Обработчик попаданий
	public function getResourceHitbox(): ?AxisAlignedBB{
		if(!$this->resourceVisible){
			return null;
		}
		$min = (1.0 - $this->resourceSize) / 2.0;
		$max = (1.0 + $this->resourceSize) / 2.0;
		return new AxisAlignedBB(
			$min,
			$min,
			$min,
			$max,
			$max,
			$max
		);
	}
}