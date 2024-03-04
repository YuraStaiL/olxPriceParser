<?php

namespace Models;

class EmailConfirmation extends Model
{
    public string $email;
    public null|string $code;
    public $confirmation_at;

    protected string $table = 'email_confirmation';
    protected string|array $primary = 'email';
}