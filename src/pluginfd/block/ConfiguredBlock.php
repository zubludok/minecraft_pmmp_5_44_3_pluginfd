<?php

declare(strict_types=1);

namespace pluginfd\block;

use customiesdevs\customies\block\component\BlockComponents;
use customiesdevs\customies\block\component\BlockComponentsTrait;
use customiesdevs\customies\block\component\GeometryComponent;
use customiesdevs\customies\block\component\MaterialInstancesComponent;
use customiesdevs\customies\block\component\CollisionBoxComponent;
use customiesdevs\customies\block\component\LiquidDetectionComponent;
use customiesdevs\customies\block\properties\Material;
use customiesdevs\customies\block\states\BlockState;
use customiesdevs\customies\block\permutations\BlockPermutation;
use customiesdevs\customies\block\permutations\BlockPermutations;
use customiesdevs\customies\block\permutations\BlockPermutationsTrait;
use customiesdevs\customies\block\properties\Box;

use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;

use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\data\runtime\RuntimeDataDescriber;

use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

use pluginfd\variant\VoxelCatalog;

final class ConfiguredBlock extends Block implements BlockComponents,BlockPermutations{

    use BlockComponentsTrait;
    use BlockPermutationsTrait;

    public const STATE_MASK="pluginfd:voxel_mask";

    private int $mask;
    private bool $gravity;
    private string $materialKey;
    private string $voxelType;
    private array $bones;
    private int $boneCount;
    private int $maxMask;

    /* @param Material[] $materials */

	public function __construct(
		BlockIdentifier $identifier,
		string $name,
		string $geometry,
		array $materials,
		float $hardness,
		bool $gravity,
		string $materialKey,
		string $voxelType
	){
		// Сначала инициализируем поля, которые может запросить Block::__construct().
		$this->gravity=$gravity;
		$this->materialKey=$materialKey;
		$this->voxelType=$voxelType;

		$profile=VoxelCatalog::getProfile($voxelType);
		$this->bones=$profile["bones"];
		$this->boneCount=count($this->bones);

		$this->maxMask=$this->boneCount===0?1:(1<<$this->boneCount)-1;
		$this->mask=$this->maxMask;

		// Родительский конструктор вызывает describeBlockOnlyState().
		parent::__construct(
			$identifier,
			$name,
			new BlockTypeInfo(new BlockBreakInfo($hardness))
		);

		$this->initStates();
		$this->initComponents($geometry,$materials);
		$this->initPermutations();
	}

    protected function describeBlockOnlyState(RuntimeDataDescriber $w): void{
        $w->int(max(1,$this->boneCount),$this->mask);
    }

    public function hasGravity(): bool{
        return $this->gravity;
    }

    public function getMaterialKey(): string{
        return $this->materialKey;
    }

    public function getVoxelType(): string{
        return $this->voxelType;
    }

    public function getBoneCount(): int{
        return $this->boneCount;
    }

    public function hasBones(): bool{
        return $this->boneCount>0;
    }

    public function getMaxMask(): int{
        return $this->maxMask;
    }

    private function initStates(): void{
        $this->addState(new BlockState(self::STATE_MASK,range(0,$this->maxMask)));
    }

    /* @param Material[] $materials */
    private function initComponents(string $geometry,array $materials): void{
        $boneVisibility=[];

        foreach($this->bones as $bit=>$bone){
            $divisor=1<<$bit;
            $boneVisibility[$bone]="math.mod(math.floor(q.block_state('".self::STATE_MASK."') / ".$divisor."), 2) == 1";
        }

        $this->addComponent(new GeometryComponent($geometry,$boneVisibility));
        $this->addComponent(new MaterialInstancesComponent($materials));
        $this->addComponent(new CollisionBoxComponent(false));
    }

    public function getCurrentStates(): array{
        return [$this->mask];
    }

    public function serializeState(BlockStateWriter $out): void{
        $out->writeInt(self::STATE_MASK,$this->mask);
    }

    public function deserializeState(BlockStateReader $in): void{
        $this->mask=$in->readBoundedInt(self::STATE_MASK,0,$this->maxMask);
        $this->collisionBoxes=null;
    }

    public function getMask(): int{
        return $this->mask;
    }

    public function isBoneVisible(int $bit): bool{
        if($bit<0 || $bit>=$this->boneCount){
            return false;
        }
        return ($this->mask&(1<<$bit))!==0;
    }

    public function setMask(int $mask): self{
        if($mask<0 || $mask>$this->maxMask){
            throw new \InvalidArgumentException("Voxel mask must be between 0 and ".$this->maxMask);
        }

        // Для обычного куба маска всегда означает цельный блок.
        if($this->boneCount===0 && $mask!==1){
            throw new \InvalidArgumentException("Cube voxel mask must be 1");
        }

        $this->mask=$mask;
        $this->collisionBoxes=null;
        return $this;
    }

    /**
     * Возвращает бит кости по локальным координатам внутри блока.
     */
	public function getBoneBitAt(float $x,float $y,float $z): ?int{
		if($this->boneCount===0){
			return null;
		}

		if($this->voxelType==="layered" || $this->voxelType==="layered_half"){
			if($y>=1.0){
				return $this->getOuterLayerBit(true);
			}

			if($y<=0.0){
				return $this->getOuterLayerBit(false);
			}
		}

		$x=max(0.0,min(0.999999,$x));
		$y=max(0.0,min(0.999999,$y));
		$z=max(0.0,min(0.999999,$z));

		$east=$x<0.5;
		$south=$z>=0.5;

		// Порядок: NW, SW, NE, SE.
		$corner=!$east
			?($south?1:0)
			:($south?3:2);

		return match($this->voxelType){
			"cubev"=>
				($y>=7/16?0:4)+$corner,

			"layered"=>
				$y<4/16?0:
				($y<8/16?1:
				($y<11/16?2:3)),

			"layered_half"=>
				$y<7/16?0:1,

			"columnar"=>match($corner){
				0=>3,
				1=>2,
				2=>0,
				3=>1
			},

			default=>null
		};
	}

	private function getOuterLayerBit(bool $top): ?int{
		if($top){
			for($i=$this->boneCount-1;$i>=0;--$i){
				if(($this->mask&(1<<$i))!==0){
					return $i;
				}
			}
		}else{
			for($i=0;$i<$this->boneCount;++$i){
				if(($this->mask&(1<<$i))!==0){
					return $i;
				}
			}
		}

		return null;
	}
	
    /**
     * Для слоистых блоков разрешено снимать только крайние слои.
     */
    public function canRemoveBone(int $bit): bool{
        if($bit<0 || $bit>=$this->boneCount){
            return false;
        }

        if(($this->mask&(1<<$bit))===0){
            return false;
        }

        if($this->voxelType!=="layered" && $this->voxelType!=="layered_half"){
            return true;
        }

        $lowest=null;
        $highest=null;

        for($i=0;$i<$this->boneCount;++$i){
            if(($this->mask&(1<<$i))!==0){
                $lowest??=$i;
                $highest=$i;
            }
        }

        return $bit===$lowest || $bit===$highest;
    }

    /**
     * Координаты части блока в системе Bedrock: X/Z -8..8, Y 0..16.
     *
     * @return array{float,float,float,float,float,float}
     */
	private function getBoneBounds(int $bit): array{
		if($this->voxelType==="cube"){
			return [-8,0,-8,8,16,8];
		}

		if($this->voxelType==="layered"){
			$bounds=[
				[0,4],
				[4,8],
				[8,12],
				[10,14]
			];

			return [-8,$bounds[$bit][0],-8,8,$bounds[$bit][1],8];
		}

		if($this->voxelType==="layered_half"){
			$bounds=[
				[0,8],
				[6,14]
			];

			return [-8,$bounds[$bit][0],-8,8,$bounds[$bit][1],8];
		}

		if($this->voxelType==="columnar"){
			$corner=match($bit){
				0=>2,
				1=>3,
				2=>1,
				3=>0
			};

			$east=$corner>=2;
			$south=$corner===1 || $corner===3;

			return [
				$east?0:-8,
				0,
				$south?0:-8,
				$east?8:0,
				16,
				$south?8:0
			];
		}

		// cubev:
		// 0 - верхний северо-запад
		// 1 - верхний юго-запад
		// 2 - верхний северо-восток
		// 3 - верхний юго-восток
		// 4 - нижний северо-запад
		// 5 - нижний юго-запад
		// 6 - нижний северо-восток
		// 7 - нижний юго-восток

		$corner=$bit%4;

		$east=$corner>=2;
		$south=$corner===1 || $corner===3;
		$upper=$bit<4;

		return [
			$east?0:-8,
			$upper?6:0,
			$south?0:-8,
			$east?8:0,
			$upper?14:8,
			$south?8:0
		];
	}

    protected function recalculateCollisionBoxes(): array{
        $boxes=[];

        for($bit=0;$bit<$this->boneCount;++$bit){
            if(($this->mask&(1<<$bit))===0){
                continue;
            }

            [$minX,$minY,$minZ,$maxX,$maxY,$maxZ]=$this->getBoneBounds($bit);

            $boxes[]=new AxisAlignedBB(
                ($minX+8)/16,
                $minY/16,
                ($minZ+8)/16,
                ($maxX+8)/16,
                $maxY/16,
                ($maxZ+8)/16
            );
        }

        if($this->boneCount===0 && $this->mask!==0){
            $boxes[]=new AxisAlignedBB(0,0,0,1,1,1);
        }

        return $boxes;
    }

    private function initPermutations(): void{
        for($mask=0;$mask<=$this->maxMask;++$mask){
            $collision=new CollisionBoxComponent($mask!==0);
            $boxes=[];

            for($bit=0;$bit<$this->boneCount;++$bit){
                if(($mask&(1<<$bit))===0){
                    continue;
                }

                [$minX,$minY,$minZ,$maxX,$maxY,$maxZ]=$this->getBoneBounds($bit);

                $boxes[]=new Box(
                    new Vector3($minX,$minY,$minZ),
                    new Vector3(
                        $maxX-$minX,
                        $maxY-$minY,
                        $maxZ-$minZ
                    )
                );
            }

            if($this->boneCount===0 && $mask!==0){
                $boxes[]=new Box(
                    new Vector3(-8,0,-8),
                    new Vector3(16,16,16)
                );
            }

            if($boxes!==[]){
                $collision->addBoxes($boxes);
            }

            $condition="q.block_state('".self::STATE_MASK."') == ".$mask;

            $liquid=new LiquidDetectionComponent(
                true,
                LiquidDetectionComponent::LIQUID_WATER,
                $mask===$this->maxMask
                    ?LiquidDetectionComponent::BLOCKING
                    :LiquidDetectionComponent::NO_REACTION
            );

            $this->addPermutation(new BlockPermutation($condition,[$collision,$liquid]));
        }
    }
}