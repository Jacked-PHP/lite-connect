<?php

namespace Tests\Feature;

use Faker\Factory;
use Faker\Generator;
use JackedPhp\LiteConnect\Connection\Connection;
use JackedPhp\LiteConnect\Migration\MigrationManager;
use JackedPhp\LiteConnect\SQLiteFactory;
use RuntimeException;
use Tests\Samples\CreateUsersTable;
use Tests\Samples\User;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;

function fake() {
    global $faker;

    if (!($faker instanceof Generator)) {
        $faker = Factory::create();
    }

    return $faker;
}

function startDatabase() {
    $databaseFile = __DIR__ . '/../Samples/test.db';
    if (file_exists($databaseFile)) {
        unlink($databaseFile);
        touch($databaseFile);
    }

    /** @var Connection $connection */
    $connection = SQLiteFactory::make([
        'database' => $databaseFile,
    ]);
    $migrationManager = new MigrationManager($connection);
    $migrationManager->runMigrations([
        new CreateUsersTable(),
    ]);

    return $connection;
}

function createUser(
    Connection $connection,
    ?string $name = null,
    ?string $email = null,
): User {
    $user = new User($connection);
    return $user->create([
        'name' => $name ?? fake()->name(),
        'email' => $email ?? fake()->email(),
    ]);
}

/**
 * @param Connection $connection
 * @return array<mixed>
 */
function getAllUsers(Connection $connection): array {
    $pdo = $connection->getPDO();
    $stmt = $pdo->prepare('SELECT * FROM users');
    $stmt->execute();

    return $stmt->fetchAll();
}

test('test can access with access through pdo', function () {
    /** @var Connection $connection */
    $connection = startDatabase();

    $expectedName = 'John Doe';
    $expectedEmail = 'joe@doe.com';

    assertCount(0, getAllUsers($connection));

    $pdo = $connection->getPDO();

    $pdo->prepare('INSERT INTO users (name, email) VALUES (:name, :email)')
        ->execute([
            'name' => $expectedName,
            'email' => $expectedEmail,
        ]);

    $result = getAllUsers($connection);

    assertCount(1, $result);
    assertEquals($expectedName, $result[0]['name']);
    assertEquals($expectedEmail, $result[0]['email']);
});

test('test can access with access through model', function () {
    /** @var Connection $connection */
    $connection = startDatabase();

    $expectedName = 'John Doe';
    $expectedEmail = 'joe@doe.com';

    assertCount(0, getAllUsers($connection));

    $user = createUser($connection, $expectedName, $expectedEmail);

    assertCount(1, getAllUsers($connection));
    assertEquals($expectedName, $user->name);
    assertEquals($expectedEmail, $user->email);
});

test('test can filter records with where statement', function () {
    /** @var Connection $connection */
    $connection = startDatabase();

    $name1 = 'Test 1';
    $name2 = 'Test 2';
    $name3 = 'Test 3';

    assertCount(0, getAllUsers($connection));

    createUser($connection, name: $name1);
    createUser($connection, name: $name2);
    createUser($connection, name: $name3);

    assertCount(3, getAllUsers($connection));

    $user1 = new User($connection);
    $result1 = $user1->where('name', '=', $name1)->get();
    assertCount(1, $result1);
    assertEquals($name1, $result1[0]->name);

    $user2 = new User($connection);
    $result2 = $user2->where('name', '=', $name2)->get();
    assertCount(1, $result2);
    assertEquals($name2, $result2[0]->name);

    $user3 = new User($connection);
    $result3 = $user3->where('name', '=', $name3)->get();
    assertCount(1, $result3);
    assertEquals($name3, $result3[0]->name);

    $user4 = new User($connection);
    $result4 = $user4->where('name', 'like', '%Test%')->get();
    assertCount(3, $result4);
    assertEquals($name1, $result4[0]->name);
    assertEquals($name2, $result4[1]->name);
    assertEquals($name3, $result4[2]->name);
});

test('test can order records', function () {
    /** @var Connection $connection */
    $connection = startDatabase();

    $name1 = 'Test 1';
    $name2 = 'Test 2';
    $name3 = 'Test 3';

    assertCount(0, getAllUsers($connection));

    createUser($connection, name: $name1);
    createUser($connection, name: $name2);
    createUser($connection, name: $name3);

    $user = new User($connection);
    $result = $user
        ->where('name', 'like', '%Test%')
        ->orderBy('id', 'desc')
        ->get();
    assertCount(3, $result);
    assertEquals($name1, $result[2]->name);
    assertEquals($name2, $result[1]->name);
    assertEquals($name3, $result[0]->name);
});

test('test can find record', function () {
    /** @var Connection $connection */
    $connection = startDatabase();

    $name1 = 'Test 1';
    $name2 = 'Test 2';
    $name3 = 'Test 3';

    assertCount(0, getAllUsers($connection));

    $record1 = createUser($connection, name: $name1);
    $record2 = createUser($connection, name: $name2);
    $record3 = createUser($connection, name: $name3);

    assertCount(3, getAllUsers($connection));

    $user1 = new User($connection);
    $result1 = $user1->find($record1->id);
    assertEquals($name1, $result1->name);

    $user2 = new User($connection);
    $result2 = $user2->find($record2->id);
    assertEquals($name2, $result2->name);

    $user3 = new User($connection);
    $result3 = $user3->find($record3->id);
    assertEquals($name3, $result3->name);
});

test('test can delete record', function () {
    /** @var Connection $connection */
    $connection = startDatabase();

    $name1 = 'Test 1';
    $name2 = 'Test 2';
    $name3 = 'Test 3';

    assertCount(0, getAllUsers($connection));

    $record1 = createUser($connection, name: $name1);
    createUser($connection, name: $name2);
    createUser($connection, name: $name3);

    assertCount(3, getAllUsers($connection));

    $user1 = new User($connection);
    $user1->find($record1->id)->delete();

    assertCount(2, getAllUsers($connection));
});

test('test can delete all records', function () {
    /** @var Connection $connection */
    $connection = startDatabase();

    $name1 = 'Test 1';
    $name2 = 'Test 2';
    $name3 = 'Test 3';

    assertCount(0, getAllUsers($connection));

    createUser($connection, name: $name1);
    createUser($connection, name: $name2);
    createUser($connection, name: $name3);

    assertCount(3, getAllUsers($connection));

    $users = new User($connection);
    $users->delete();

    assertCount(0, getAllUsers($connection));
});

test('test can close connection', function () {
    /** @var Connection $connection */
    $connection = startDatabase();

    createUser($connection);
    createUser($connection);
    createUser($connection);
    assertCount(3, getAllUsers($connection));

    $connection->close();

    getAllUsers($connection);
})->expectExceptionObject(new RuntimeException('Connection is closed'));
