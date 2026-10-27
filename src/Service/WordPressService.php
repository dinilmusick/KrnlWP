<?php
namespace KrnlWP\Service;
use KrnlWP\Contract\Service;
use KrnlWP\Lifecycle\LifecycleManager;
final class WordPressService implements Service {
    private LifecycleManager $lifecycle;
    private array $state;
    private array $listeners=[];
    private bool $initialized=false;
    public function __construct(private readonly Definition $definition){
        $this->lifecycle=new LifecycleManager();$this->state=$definition->state;
    }
    public function id():string{return $this->definition->id;}
    public function version():string{return $this->definition->version;}
    public function state():string{return $this->lifecycle->status();}
    public function register():void{
        if($this->initialized)return;
        $this->lifecycle->transition('initializing');$this->lifecycle->transition('ready');$this->initialized=true;
    }
    public function start():void{
        if($this->state()==='running')return;
        $this->lifecycle->transition('starting');
        try{$this->definition->lifecycle['onStart']??null;$this->lifecycle->transition('running');}
        catch(\Throwable $e){$this->lifecycle->transition('ready');throw $e;}
    }
    public function stop():void{
        if($this->state()==='stopped')return;
        $this->lifecycle->transition('stopping');
        try{$this->lifecycle->transition('stopped');}
        catch(\Throwable $e){$this->lifecycle->transition('running');throw $e;}
    }
    public function call(string $operation,mixed $input=[],array $context=[]):mixed{
        $this->lifecycle->assertRunning($this->id);
        $handler=$this->definition->operations[$operation]??null;
        if(!is_callable($handler))throw new \RuntimeException("Unknown operation: $operation");
        return $handler($input,$context+['service'=>$this]);
    }
    public function getState():array{return $this->state;}
    public function setState(array $mutation):void{$this->state=array_replace($this->state,$mutation);}
    public function on(string $event,callable $handler):callable{$this->listeners[$event]??=[];$this->listeners[$event][]=$handler;return function()use($event,$handler):void{$this->listeners[$event]=array_filter($this->listeners[$event],fn($h)=>$h!==$handler);};}
    public function emit(string $event,mixed $payload=null):void{foreach($this->listeners[$event]??[] as $handler)$handler($payload,$this);}
}