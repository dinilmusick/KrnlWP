<?php
namespace KrnlWP\Orchestration;
final class FunctionalityOrchestration {
    private array $steps=[];
    public function __construct(public readonly string $id,public readonly string $version='1.0.0') {}
    public function then(string $functionality,array $options=[]):self {
        $id=$options['id']??'step_'.count($this->steps).'_'.$functionality;
        $depends=$options['dependsOn']??(count($this->steps)?[$this->steps[array_key_last($this->steps)]->id]:[]);
        $this->steps[]=new Step($id,'functionality',$functionality,$depends,$options['condition']??null);
        return $this;
    }
    public function parallel(string $name,array $targets,array $options=[]):self {
        $depends=$options['dependsOn']??(count($this->steps)?[$this->steps[array_key_last($this->steps)]->id]:[]);
        $ids=[];foreach($targets as $target){$id=$name.'_'.$target;$ids[]=$id;$this->steps[]=new Step($id,'functionality',$target,$depends);}
        $this->steps[]=new Step($name.'_join','fanIn',$name,$ids);return $this;
    }
    public function branch(string $name,callable $condition,string $then,?string $else=null):self {
        $depends=count($this->steps)?[$this->steps[array_key_last($this->steps)]->id]:[];
        $a=$name.'_then';$this->steps[]=new Step($a,'functionality',$then,$depends,$condition);
        $join=[$a];if($else){$b=$name.'_else';$this->steps[]=new Step($b,'functionality',$else,$depends,fn($ctx)=>!$condition($ctx));$join[]=$b;}
        $this->steps[]=new Step($name.'_join','fanIn',$name,$join);return $this;
    }
    public function fanOut(string $name,string $target,callable $items,int $concurrency=5):self {
        $depends=count($this->steps)?[$this->steps[array_key_last($this->steps)]->id]:[];
        $this->steps[]=new Step($name,'fanOut',$target,$depends,null,$concurrency);return $this;
    }
    public function nested(string $id):self{return $this->then($id,['id'=>'nested_'.$id,'type'=>'nested']);}
    public function steps():array{return $this->steps;}
    public function definition():array{return ['id'=>$this->id,'version'=>$this->version,'scope'=>'functionality','steps'=>array_map(fn(Step $s)=>['id'=>$s->id,'type'=>$s->type,'target'=>$s->target,'dependsOn'=>$s->dependsOn],$this->steps)];}
}