<?php
namespace KrnlWP\Contract;

interface Functionality {
    public function id(): string;
    public function execute(array $input = []): mixed;
}