<?php

declare(strict_types=1);

namespace App\Core;

class MiddlewareChain
{
    public function __construct(
        private readonly array $middlewares,
        private readonly mixed $finalHandler
    ) {}

    public function run(Request $request): void
    {
        $this->execute($request, 0);
    }

    private function execute(Request $request, int $index): void
    {
        if ($index >= count($this->middlewares)) {
            ($this->finalHandler)($request);
            return;
        }

        $middleware = $this->middlewares[$index];
        $middleware->handle($request, function (Request $req) use ($index) {
            $this->execute($req, $index + 1);
        });
    }
}
