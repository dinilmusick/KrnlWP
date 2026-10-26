<?php
namespace KrnlWP\Service;
final class Definition {
    public function __construct(
        public readonly string $id, public readonly string $name, public readonly string $version,
        public readonly array $operations=[], public readonly array $state=[],
        public readonly array $events=[], public readonly array $dependencies=[], public readonly array $capabilities=[]
    ) {}
}