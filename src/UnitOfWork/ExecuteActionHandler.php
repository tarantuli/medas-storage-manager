<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\Core\Attributes\{EventListener, Service};
use Medas\StorageManager\Events\ExecuteAction;

#[Service]
readonly class ExecuteActionHandler
{
    public function __construct(
        private UnitOfWorkExecutor $executor,
    )
    {
    }

    #[EventListener]
    public function handle(ExecuteAction $event): void
    {
        $unitOfWork = new UnitOfWork();

        $unitOfWork->addAction($event->action);

        $this->executor->execute($unitOfWork);
    }
}
