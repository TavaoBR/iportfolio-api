<?php

declare(strict_types=1);

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateUserDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'O nome e obrigatorio')]
        #[Assert\Type(type: 'string', message: 'O nome deve ser um texto')]
        #[Assert\Length(max: 150, maxMessage: 'O nome deve ter no maximo {{ limit }} caracteres')]
        public string $name,

        #[Assert\NotBlank(message: 'O email e obrigatorio')]
        #[Assert\Type(type: 'string', message: 'O email deve ser um texto')]
        #[Assert\Email(message: 'Informe um email valido')]
        #[Assert\Length(max: 180, maxMessage: 'O email deve ter no maximo {{ limit }} caracteres')]
        public string $email,

        #[Assert\NotBlank(message: 'A senha e obrigatoria')]
        #[Assert\Type(type: 'string', message: 'A senha deve ser um texto')]
        #[Assert\Length(
            min: 8,
            max: 72,
            minMessage: 'A senha deve ter pelo menos {{ limit }} caracteres',
            maxMessage: 'A senha deve ter no maximo {{ limit }} caracteres'
        )]
        #[Assert\Regex(
            pattern: '/^(?=.*[A-Za-z])(?=.*\d).+$/',
            message: 'A senha deve conter letras e numeros'
        )]
        public string $password,

        #[Assert\Length(
            max: 3_500_000,
            maxMessage: 'O avatar excede o tamanho maximo permitido'
        )]
        public ?string $avatar = null,
    ) {
    }
}