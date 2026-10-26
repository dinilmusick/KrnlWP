<?php
namespace KrnlWP\Contract;

interface Service {
    public function id(): string;
    public function version(): string;
    public function register(): void;
    public function start(): void;
    public function stop(): void;
    public function state(): string;
}