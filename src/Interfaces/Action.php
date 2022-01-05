<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

interface Action
{
    public function execute(): void;

    public function storage(): Storage;

    public function onComplete(): \Closure|null;

    public function setOnComplete(\Closure|null $onComplete): self;
}
