<?php
namespace KrnlWP\Contract;

interface ProtocolAdapter {
    public function call(string $service,string $operation,array $input=[]): mixed;
}