<?php
namespace KrnlWP\Runtime;

use KrnlWP\Contract\Service;
use RuntimeException;

final class ServiceRuntime {
    public function __construct(private Service $service) {}

    public function initialize(): void {
        $this->transition(Lifecycle::INITIALIZING);
        $this->service->register();
        $this->transition(Lifecycle::READY);
    }

    public function start(): void {
        $this->transition(Lifecycle::STARTING);
        $this->service->start();
        $this->transition(Lifecycle::RUNNING);
    }

    public function stop(): void {
        $this->transition(Lifecycle::STOPPING);
        $this->service->stop();
        $this->transition(Lifecycle::STOPPED);
    }

    private function transition(string $next): void {
        if (!Lifecycle::canTransition($this->service->state(),$next)) {
            throw new RuntimeException("Invalid lifecycle transition");
        }
    }
}