<?php

declare(strict_types=1);

namespace pluginfd\variant;

final class BlockVariantDefinition{

    public function __construct(
        public readonly string $suffix,
        public readonly string $geometry
    ){}
}