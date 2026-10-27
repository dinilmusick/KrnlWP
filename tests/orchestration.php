<?php
require __DIR__.'/../vendor/autoload.php';

use KrnlWP\Orchestration\Executor;
use KrnlWP\Orchestration\FunctionalityOrchestration;

function expect($condition,string $message):void{
    if(!$condition)throw new RuntimeException($message);
}

$flow=new FunctionalityOrchestration('test');
$flow->then('double')->then('add');
$result=(new Executor())->execute($flow,[
    'double'=>fn(array $ctx)=>$ctx['results']['input']['items']['value']*2,
    'add'=>fn(array $ctx)=>$ctx['results']['step_0_add']??$ctx['results']['step_0_double']+3,
],['value'=>7]);
expect($result===17,'sequential execution failed');

$branch=new FunctionalityOrchestration('branch');
$branch->branch('decision',fn(array $ctx)=>$ctx['results']['input']['ok'],'yes','no');
$result=(new Executor())->execute($branch,['yes'=>fn()=> 'yes','no'=>fn()=> 'no'],[ 'ok'=>true ]);
expect($result['decision_then']==='yes' && $result['decision_else']===null,'branch execution failed');

$fan=new FunctionalityOrchestration('fan');
$fan->fanOut('items','square',fn(array $ctx)=>$ctx['results']['input'],2);
$result=(new Executor())->execute($fan,['square'=>fn($v)=>$v*$v],[1,2,3]);
expect($result===[1,4,9],'fanOut execution failed');

$retry=new FunctionalityOrchestration('retry');
$retry->then('flaky',['retry'=>['maxAttempts'=>3,'backoffMs'=>0]]);
$attempts=0;
$result=(new Executor())->execute($retry,['flaky'=>function()use(&$attempts){$attempts++;if($attempts<3)throw new RuntimeException('retry');return 'ok';}],[]);
expect($result==='ok' && $attempts===3,'retry execution failed');

echo "orchestration tests: OK\n";
