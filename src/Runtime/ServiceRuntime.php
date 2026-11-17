<?php

declare(strict_types=1);

namespace KrnlWP\Runtime;

use KrnlWP\Contract\Service;
use KrnlWP\Registry;

final class ServiceRuntime {
    private array $services=[];
    public readonly Registry $registry;

    public function __construct(?Registry $registry=null){$this->registry=$registry??new Registry();}

    public function register(Service $service):self{
        $id=$service->id();
        if (isset($this->services[$id])) {
            if ($this->services[$id] !== $service) throw new \RuntimeException("Service already registered: $id");
            return $this;
        }
        $this->registry->register($service);
        $this->services[$id]=$service;
        return $this;
    }
    public function unregister(string $id):bool{
        $removed=isset($this->services[$id]);unset($this->services[$id]);$this->registry->unregister($id);return $removed;
    }
    public function get(string $id):?Service{return $this->services[$id]??null;}
    public function list():array{return array_values($this->services);}
    public function execute(string $serviceId,string $operation,mixed $input=[],array $context=[]):mixed{
        $service=$this->get($serviceId);
        if(!$service)throw new \RuntimeException("Target service not found: $serviceId");
        return $service->call($operation,$input,$context);
    }
    public function startAll():void{
        $started=[];
        try {
            foreach ($this->services as $service) {
                // Preserve running services owned by the caller during rollback.
                if ($service->state() === 'running') continue;
                $service->register();
                $service->start();
                $started[] = $service;
            }
        } catch (\Throwable $e) {
            foreach (array_reverse($started) as $service) {
                try { if ($service->state() === 'running') $service->stop(); }
                catch (\Throwable $ignored) {}
            }
            throw $e;
        }
    }
    public function stopAll():void{
        $first=null;
        foreach (array_reverse($this->services) as $service) {
            if ($service->state() !== 'running') continue;
            try { $service->stop(); } catch (\Throwable $e) { $first ??= $e; }
        }
        if($first)throw $first;
    }
}