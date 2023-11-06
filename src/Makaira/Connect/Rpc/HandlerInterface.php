<?php

namespace Makaira\Connect\Rpc;

interface HandlerInterface
{
    public function handle(array $request): array;
}
