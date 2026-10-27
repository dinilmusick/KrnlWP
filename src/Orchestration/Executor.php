<?php
namespace KrnlWP\Orchestration;
final class Executor {
    public function execute(FunctionalityOrchestration $flow,array $functionalities,array $input=[]):mixed{
        $done=[];$value=$input;
        foreach($flow->steps() as $step){
            foreach($step->dependsOn as $dep)if(!array_key_exists($dep,$done))throw new \RuntimeException("Unresolved dependency: $dep");
            if($step->type==='fanIn'){$done[$step->id]=$value;continue;}
            if($step->type==='fanOut'){
                $items=$step->inputFactory?($step->inputFactory)($value):[];
                $worker=$functionalities[$step->target]??null;
                if(!is_callable($worker))throw new \RuntimeException("Unknown functionality: ".$step->target);
                $results=[];foreach((array)$items as $item)$results[]=$worker($item);$value=$results;
                $done[$step->id]=$value;continue;
            }
            if($step->condition && !$step->condition($value)){$done[$step->id]=null;continue;}
            $fn=$functionalities[$step->target]??null;
            if(!is_callable($fn))throw new \RuntimeException("Unknown functionality: ".$step->target);
            if($step->type==='nested')$value=$fn($value);else$value=$fn($value);
            $done[$step->id]=$value;
        }
        return $value;
    }
}