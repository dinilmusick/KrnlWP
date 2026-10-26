<?php
namespace KrnlWP;

use KrnlWP\Contract\Service;
use RuntimeException;

final class Registry {
    /** @var array<string,Service> */
    private array $services=[];

    public function register(Service $service): void {
        if (isset($this->services[$service->id()])) {
            throw new RuntimeException("Service already registered: ".$service->id());
        }
        $this->services[$service->id()]=$service;
    }

    public function get(string $id): Service {
        return $this->services[$id] ?? throw new RuntimeException("Unknown service: ".$id);
    }

    /** @return list<Service> */
    public function all(): array { return array_values($this->services); }
}