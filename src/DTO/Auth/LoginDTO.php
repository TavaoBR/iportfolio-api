<?php

declare(strict_types=1);

namespace App\DTO\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class LoginDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email e obrigatorio')]
        #[Assert\Email(message: 'Email invalido')]
        public readonly string $email,

        #[Assert\NotBlank(message: 'Senha e obrigatoria')]
        public readonly string $password,
    ) {
    }
}