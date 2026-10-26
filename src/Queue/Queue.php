<?php
namespace KrnlWP\Queue;

interface Queue {
    public function dispatch(object $message): string;
}