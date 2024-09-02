
# LiteConnect

[![Tests](https://github.com/Jacked-PHP/lite-connect/actions/workflows/php.yml/badge.svg)](https://github.com/Jacked-PHP/lite-connect/actions/workflows/php.yml)

LiteConnect is a simple, lightweight SQLite package for PHP without globals. It is designed to facilitate easy and efficient SQLite database interactions. It is ideal for small to medium-sized projects that require an embedded database solution. This package provides a clean API for managing SQLite connections, running migrations, and interacting with your data models.

## Features

- **Connection Management**: Create and manage SQLite connections.
- **Migration Management**: Run migrations to set up your database schema.
- **Model Interaction**: Perform common database operations like `create`, `find`, `update`, `delete`, `where`, and `orderBy` through an intuitive API.

## Installation

To install LiteConnect, you can require it via Composer:

```bash
composer require jacked-php/lite-connect
```

## Basic Usage

### Connecting to a SQLite Database

```php
use JackedPhp\LiteConnect\Connection\Connection;
use JackedPhp\LiteConnect\SQLiteFactory;

/** @var Connection $connection */
$connection = SQLiteFactory::make([
    'database' => 'path/to/your/database.db',
]);

// When you're done with the connection:
$connection->close();
```

### Running Migrations

To set up your database schema, use the `MigrationManager` to run migrations.

Example with a "users" table migration:

```php
use JackedPhp\LiteConnect\Migration\MigrationManager;

class CreateUsersTable implements Migration
{

    public function up(PDO $pdo): void
    {
        $pdo->exec('CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NULL,
            email TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE users');
    }
}

$migrationManager = new MigrationManager($connection);
$migrationManager->runMigrations([
    new CreateUsersTable(),
]);
```

### Interacting with Models

You can interact with your data using the model classes. Here is an example of a `User` model:

```php
use JackedPhp\LiteConnect\Model\BaseModel;

class User extends BaseModel
{
    protected string $table = 'users';

    protected ?string $primaryKey = 'id';

    /**
     * @var string[] $fillable
     */
    protected array $fillable = [
        'name',
        'email',
    ];
}


// Creating a new user
/** @var User $newUser */
$newUser = (new User($connection))->create([
    'name' => 'John Doe',
    'email' => 'john.doe@example.com',
]);

// Finding a user by ID
/** @var User $foundUser */
$foundUser = (new User($connection))->find($newUser->id);

// Updating a user
$foundUser->update([
    'email' => 'john.doe@newdomain.com',
]);

// Deleting a user
$foundUser->delete();
// or
(new User($connection))->where('name', '=', 'John Doe')->delete();
```

### Querying Data

You can use the `where`, `orderBy`, and other query methods to filter and order your data:

```php
$users = new User($connection);

$filteredUsers = $users->where('name', '=', 'John Doe')->get();
// or
$orderedUsers = $users->orderBy('id', 'desc')->get();
```

## Testing

You can run tests by running the following after cloning the repository and installing dependencies:

```bash
vendor/bin/pest
```

## Contributing

If you would like to contribute to LiteConnect, please feel free to submit pull requests or open issues on the [GitHub repository](https://github.com/Jacked-PHP/liteconnect).

## License

LiteConnect is open-sourced software licensed under the [MIT license](LICENSE).
