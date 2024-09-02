<?php

namespace Tests\Samples;

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
