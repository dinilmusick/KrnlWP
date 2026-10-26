<?php
namespace KrnlWP;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class Kernel {
    public const VERSION='0.2.0';
    public function __construct(private ContainerBuilder $container=new ContainerBuilder()) {}
    public function register(object $service,string $id): self {
        $this->container->set($id,$service); return $this;
    }
    public function compile(): void { $this->container->compile(); }
    public function get(string $id): object { return $this->container->get($id); }
    public static function events(): EventDispatcher { return new EventDispatcher(); }
}