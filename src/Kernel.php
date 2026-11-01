<?php
declare(strict_types=1);

namespace KrnlWP;

use KrnlWP\Telemetry\TelemetryController;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class Kernel {
    public const VERSION = '0.3.0';
    private TelemetryController $telemetry;

    public function __construct(private ContainerBuilder $container = new ContainerBuilder()) {
        $this->telemetry = new TelemetryController();
    }

    public function register(object $service, string $id): self {
        $this->container->set($id, $service);
        return $this;
    }

    public function compile(): void {
        $this->container->compile();
    }

    public function get(string $id): object {
        return $this->container->get($id);
    }

    public function telemetry(): TelemetryController {
        return $this->telemetry;
    }

    public static function events(): EventDispatcher {
        return new EventDispatcher();
    }
}