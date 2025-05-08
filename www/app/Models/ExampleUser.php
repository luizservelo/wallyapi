<?php

namespace App\Models;

use App\Core\Model;

class ExampleUser extends Model {
    public function __construct()
    {
        parent::__construct(
            'example_users',
            [
                'user_email',
                'user_password',
                'user_name'
            ],
            'user_id',
            true // Chave primária é UUID (true)
        );
    }
} 