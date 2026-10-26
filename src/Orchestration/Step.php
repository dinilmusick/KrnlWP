<?php
namespace KrnlWP\Orchestration;
final class Step {
    public function __construct(
        public readonly string $id, public readonly string $type, public readonly string $target,
        public readonly array $dependsOn=[], public readonly ?\Closure $condition=null,
        public readonly ?int $concurrency=null
    ) {}
}