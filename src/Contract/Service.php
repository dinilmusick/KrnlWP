<?php
namespace KrnlWP\Contract;
interface Service {
    public function id(): string;
    public function version(): string;
    public function register(): void;
    public function start(): void;
    public function stop(): void;
    public function state(): string;
    public function call(string $operation,mixed $input=[],array $context=[]): mixed;
}