<?php
namespace KrnlWP\Runtime;

final class ServiceRuntime {
    public function __construct(private ManagedService $service) {}
    public function initialize(): void { $this->service->initialize(); }
    public function start(): void { $this->service->boot(); }
    public function stop(): void { $this->service->shutdown(); }
}