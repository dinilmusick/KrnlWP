<?php
namespace KrnlWP\Orchestration;
final class Executor {
    public function execute(FunctionalityOrchestration $flow,array $functionalities,array $input=[]):mixed {
        $done=[];$value=$input;
        foreach($flow->steps() as $step){
            foreach($step->dependsOn as $dep)if(!isset($done[$dep]))throw new \RuntimeException("Unresolved dependency: $dep");
            if($step->condition && !$step->condition($value)){$done[$step->id]=null;continue;}
            if($step->type==='fanIn'){$done[$step->id]=$value;continue;}
            $fn=$functionalities[$step->target]??null;
            if(!is_callable($fn))throw new \RuntimeException("Unknown functionality: ".$step->target);
            if($step->type==='fanOut'){$items=$fn($value);$results=[];foreach((array)$items as $item)$results[]=$functionalities[$step->target]($item);$value=$results;}
            else{$value=$fn($value);}
            $done[$step->id]=$value;
        }
        return $value;
    }
}