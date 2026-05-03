<?php

declare(strict_types=1);

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateUserDTO
{
    public function __construct(
        #[Assert\Type(type: 'string', message: 'O nome deve ser um texto')]
        #[Assert\Length(max: 150, maxMessage: 'O nome deve ter no maximo {{ limit }} caracteres')]
        public ?string $name = null,

        #[Assert\Type(type: 'string', message: 'O email deve ser um texto')]
        #[Assert\Email(message: 'Informe um email valido')]
        #[Assert\Length(max: 180, maxMessage: 'O email deve ter no maximo {{ limit }} caracteres')]
        public ?string $email = null,

        public ?string $avatar = null,
    ) {
    }
}