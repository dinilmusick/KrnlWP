<?php
require __DIR__.'/../vendor/autoload.php';
use KrnlWP\Service\{Definition,WordPressService};
function expect($ok,$msg){if(!$ok)throw new RuntimeException($msg);}
$s=new WordPressService(new Definition('demo','Demo','1.0',['echo'=>fn($in)=>$in],['ready'=>true]));
expect($s->state()==='uninitialized','initial state');
$s->register();expect($s->state()==='ready','register');
try{$s->call('echo','x');throw new RuntimeException('call should reject');}catch(RuntimeException $e){}
$s->start();expect($s->state()==='running','start');
expect($s->call('echo','ok')==='ok','call');
$s->stop();expect($s->state()==='stopped','stop');
echo "service lifecycle tests: OK\n";
