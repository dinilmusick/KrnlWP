<?php

declare(strict_types=1);

if (is_file(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'KrnlWP\\';
        if (!str_starts_with($class, $prefix)) return;
        $path = __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($path)) require_once $path;
    });
}

use KrnlWP\Contract\Service;
use KrnlWP\Runtime\ServiceRuntime;

function verify(bool $value, string $message): void {
    if (!$value) throw new RuntimeException($message);
}

final class TestService implements Service {
    public string $status = 'uninitialized';
    public int $starts = 0;
    public int $stops = 0;
    public bool $failStart = false;
    public function __construct(private string $identifier) {}
    public function id(): string { return $this->identifier; }
    public function version(): string { return '1.0'; }
    public function register(): void { if ($this->status === 'uninitialized') $this->status = 'ready'; }
    public function start(): void {
        $this->starts++;
        if ($this->failStart) throw new RuntimeException('start failure');
        $this->status = 'running';
    }
    public function stop(): void { $this->stops++; $this->status = 'stopped'; }
    public function state(): string { return $this->status; }
    public function call(string $operation, mixed $input = [], array $context = []): mixed { return $input; }
    public function getState(): array { return []; }
    public function setState(array $mutation): void {}
    public function capabilities(): array { return []; }
    public function getDefinition(): object { return new stdClass(); }
    public function dispose(): void { $this->status = 'disposed'; }
}

$runtime = new ServiceRuntime();
$one = new TestService('shared');
$runtime->register($one)->register($one);
verify(count($runtime->list()) === 1, 'same instance should register idempotently');
try {
    $runtime->register(new TestService('shared'));
    throw new RuntimeException('distinct duplicate accepted');
} catch (RuntimeException $e) {
    verify(str_contains($e->getMessage(), 'already registered'), 'duplicate must be rejected');
}
verify($runtime->registry->get('shared') === $one, 'registry consistency');

$alreadyRunning = new TestService('pre-running');
$alreadyRunning->status = 'running';
$failure = new TestService('fails');
$failure->failStart = true;
$partlyRunning = new ServiceRuntime();
$partlyRunning->register($alreadyRunning)->register($failure);
try {
    $partlyRunning->startAll();
    throw new RuntimeException('expected startup failure');
} catch (RuntimeException $e) {
    verify($e->getMessage() === 'start failure', 'original failure preserved');
}
verify($alreadyRunning->stops === 0 && $alreadyRunning->state() === 'running', 'pre-running service preserved');
$partlyRunning->stopAll();
$partlyRunning->stopAll();
verify($alreadyRunning->stops === 1, 'stopAll idempotent');

$new = new TestService('new');
$failed = new TestService('failed');
$failed->failStart = true;
$rolling = new ServiceRuntime();
$rolling->register($new)->register($failed);
try { $rolling->startAll(); } catch (RuntimeException $e) {}
verify($new->stops === 1 && $new->state() === 'stopped', 'only new services rolled back');
$rolling->stopAll();
verify($new->stops === 1, 'stopped services skipped');
echo "runtime regression tests: OK\n";
