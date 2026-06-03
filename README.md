# medas-storage-manager

Part of the [Medas framework](https://github.com/tarantuli/medas-core).

## Description

The storage abstraction layer used by `medas-entity-manager`. It provides a backend-agnostic interface for CRUD operations, schema management, migrations, and transactions. Driver packages (`medas-pdo-mysql`, `medas-pdo-sqlite`, `medas-json-storage`) register `StorageController` implementations here.

**Core concepts:**

`StorageManager` holds a registry of named `Storage` backends and a registry of `StorageController` implementations. When a `Storage` is added, `StorageManager` finds the first `StorageController` that `handles()` it and calls `initialize()` on it. The controller is cached per storage name.

`UnitOfWork` collects a set of `Action` objects (INSERT, UPDATE, DELETE, SELECT, etc.) and sorts them by `Priority` before execution. `UnitOfWorkExecutor` dispatches the sorted set to the appropriate `ActionExecutor`.

`MigrationManager` discovers `Migration` classes in a directory, executes unrun ones in alphabetical order (by class name), and records each execution in a migrations’ store. Migrations that have already run are skipped. `markMigrated()` lets you mark a migration as run without executing it (e.g., for pre-existing schema changes).

`ValueSerializer` is a shared event-based serializer: it dispatches `SerializeValueRequest` and `UnserializeValueRequest` events so driver-specific serializers can intercept and transform values (e.g., UUID binary encoding, datetime formatting) without coupling to a specific backend.

**Key interfaces:**

| Interface           | Purpose                                                                     |
|---------------------|-----------------------------------------------------------------------------|
| `Storage`           | A named backend connection (e.g. a `Database` or `StorageDirectory`)        |
| `Store`             | A named table/file within a `Storage`                                       |
| `StorageController` | Handles CRUD for a specific `Storage` type                                  |
| `ActionBuilders`    | Factory for INSERT, UPDATE, DELETE, SELECT, migration store action builders |
| `ActionExecutor`    | Executes an `ActionSet`                                                     |
| `Transaction`       | `begin()`, `commit()`, `rollback()`                                         |
| `Migration`         | `migrate(UnitOfWork)` / `undo(UnitOfWork)`                                  |

## Configuration options

| Option                                                                       | Default             | Description                                                          |
|------------------------------------------------------------------------------|---------------------|----------------------------------------------------------------------|
| `storage-manager.migration-directory`                                        | `migrations`        | Directory scanned for `Migration` classes                            |
| `storage-manager.migrations-store-name`                                      | `migrations`        | Store/table name for migration execution records                     |
| `storage-manager.deadlock-retry-attempts`                                    | `3`                 | Times to retry a deadlocked transaction                              |
| `storage-manager.original-class-storage.default-strategy`                    | `LinkingStore`      | Strategy for storing the original class name of polymorphic entities |
| `storage-manager.original-class-storage.linking-store.store-naming-strategy` | `AppendFixedSuffix` | How the linking store name is derived from the entity store name     |

## Usage

### Package developer context

Register the package — it is pulled in automatically by `medas-pdo-storage` and `medas-json-storage`:

```php
use Medas\StorageManager\StorageManagerPackage;

StorageManagerPackage::instance();
```

**Registering a storage backend:**

```php
use Medas\StorageManager\StorageManager;
use Medas\PdoStorage\Database;
use Medas\Core\Attributes\Service;

#[Service]
readonly class AppBootstrap
{
    public function __construct(
        private StorageManager $storageManager,
    ) {}

    public function boot(): void
    {
        // The first storage added becomes the default
        $this->storageManager->add(
            new Database(
                dsn: 'mysql:host=127.0.0.1;dbname=my_app;charset=utf8mb4',
                username: 'app_user',
                password: 'secret',
                name: 'default',
                usePersistentConnection: false,
            ),
        );
    }
}
```

**Using `UnitOfWork` for batched writes:**

```php
use Medas\StorageManager\{StorageManager, UnitOfWork\UnitOfWork, UnitOfWork\UnitOfWorkExecutor};
use Medas\StorageManager\Queries\Query;
use Medas\StorageManager\UnitOfWork\Priority;
use Medas\Core\Attributes\Service;

#[Service]
readonly class BulkImporter
{
    public function __construct(
        private StorageManager     $storageManager,
        private UnitOfWorkExecutor $executor,
    ) {}

    public function import(array $records): void
    {
        $unitOfWork = new UnitOfWork();
        $store = $this->storageManager->controller()->store('products');

        foreach ($records as $record) {
            $actions = $this->storageManager->controller()
                ->actionBuilders()->insert()->build($store, $record);

            foreach ($actions as $action) {
                $unitOfWork->addAction($action);
            }
        }

        $this->executor->execute($unitOfWork);
    }
}
```

**Writing a migration:**

```php
use Medas\StorageManager\Migrations\Migration;
use Medas\StorageManager\UnitOfWork\UnitOfWork;
use Medas\PdoStorage\Queries\Query;
use Medas\StorageManager\UnitOfWork\Priority;

// File name determines execution order — use a timestamp prefix
class Migration20260522AddInvoiceStatusIndex implements Migration
{
    public function migrate(UnitOfWork $unitOfWork): void
    {
        $unitOfWork->addAction(new Query(
            query: 'ALTER TABLE invoices ADD INDEX idx_status (status)',
            arguments: [],
            storage: service(\Medas\StorageManager\StorageManager::class)->byName('default'),
            priority: Priority::Normal,
        ));
    }

    public function undo(UnitOfWork $unitOfWork): void
    {
        $unitOfWork->addAction(new Query(
            query: 'ALTER TABLE invoices DROP INDEX idx_status',
            arguments: [],
            storage: service(\Medas\StorageManager\StorageManager::class)->byName('default'),
            priority: Priority::Normal,
        ));
    }
}
```

**Implementing a custom `StorageController`:**

```php
use Medas\StorageManager\Interfaces\{Storage, StorageController, Store, Transaction};
use Medas\Core\Attributes\Service;

#[Service]
readonly class RedisStorageController implements StorageController
{
    public function handles(Storage $storage): bool
    {
        return $storage instanceof RedisStorage;
    }

    public function initialize(Storage $storage): void
    {
        // Connect and prepare
    }

    public function store(string $name, Storage|null $storage = null): Store
    {
        // Return a RedisStore
    }

    // ... implement the rest of the interface
}

// Register it:
service(\Medas\StorageManager\StorageManager::class)->registerController(
    service(RedisStorageController::class)
);
```

### Backend user context

**Running migrations:**

```bash
# Run all unexecuted migrations in the configured directory
php medas storage-manager:migrate

# Mark a specific migration file as executed without running it
php medas storage-manager:mark-migrated migrations/Migration20260101AddUsersTable.php
```

Migration class names must be unique across the directory. Migrations are sorted alphabetically by class name before execution, so prefixing with a timestamp (`Migration20260522…`) ensures deterministic ordering.

**Migration directory configuration:**

```yaml
storage-manager:
  migration-directory: database/migrations
  migrations-store-name: schema_migrations
  deadlock-retry-attempts: 5
```

**`UnitOfWork` action priority** — actions within a unit of work are sorted by `Priority` before execution:

| `Priority`     | Intended use                                       |
|----------------|----------------------------------------------------|
| `First`        | Schema changes (CREATE TABLE)                      |
| `BeforeNormal` | Prerequisite inserts (parent records)              |
| `Normal`       | Regular inserts, updates, deletes                  |
| `AfterNormal`  | Dependent inserts (child records, join table rows) |
| `Last`         | Post-write cleanup                                 |
