<?php
namespace KrnlWP\Protocol;
use KrnlWP\Contract\ProtocolAdapter;
final class Protocol implements ProtocolAdapter {
    public function __construct(private $sender) {}
    public function call(string $service,string $operation,array $input=[]): mixed {
        return ($this->sender)($service,$operation,$input);
    }
}