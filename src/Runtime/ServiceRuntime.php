<?php
namespace KrnlWP\Runtime;
final class ServiceRuntime {
    /** @var array<string,ManagedService> */
    private array $services=[];
    public function __construct(?ManagedService $service=null){if($service)$this->register($service);}
    public function register(ManagedService $service):self{$id=$service->id();if(isset($this->services[$id])&&$this->services[$id]!==$service)throw new \RuntimeException("Service already registered: $id");$this->services[$id]=$service;return $this;}
    public function get(string $id):?ManagedService{return $this->services[$id]??null;}
    public function list():array{return array_values($this->services);}
    public function initialize():void{foreach($this->services as $service)$service->initialize();}
    public function start():void{$started=[];try{foreach($this->services as $service){$service->boot();$started[]=$service;}}catch(\Throwable $e){foreach(array_reverse($started) as $service)try{$service->shutdown();}catch(\Throwable $ignored){}throw $e;}}
    public function stop():void{$first=null;foreach(array_reverse($this->services) as $service)try{$service->shutdown();}catch(\Throwable $e){$first??=$e;}if($first)throw $first;}
}