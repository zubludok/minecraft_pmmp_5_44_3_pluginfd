<?php

declare(strict_types=1);

namespace pluginfd\material;

use customiesdevs\customies\block\properties\Material;
use customiesdevs\customies\block\properties\RenderMethod;

final class TreeMaterialCatalog{

	public static function create(string $materialKey,bool $stripped=false,string $textureSuffix=""): array{
		$texture="pluginfd:tree_".$materialKey.$textureSuffix;
		if($stripped){
			$texture.="_str";
		}
		return [new Material(Material::TARGET_ALL,$texture,RenderMethod::ALPHA_TEST_SINGLE_SIDED_TO_OPAQUE)];
	}
}