<?php

declare(strict_types=1);

namespace App\Attribute;

/**
 * Marca endpoints HTTP da API como públicos (sem validação de sessão/token).
 *
 * Todas as rotas cujo nome começa por `api_` deve declarar também
 * ou #[RequiresAuth] ou #[PublicRoute] (nível classe ou método; método sobrescreve classe).
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final class PublicRoute
{
}
