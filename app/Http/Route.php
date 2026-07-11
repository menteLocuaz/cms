<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Value object que representa los segmentos de la URL actual.
 *
 * Encapsula el parseo de $_SERVER['REQUEST_URI'] para que las vistas
 * y los controllers no tengan que conocer la mecánica interna.
 */
final class Route
{
    /** @param string[] $segments */
    private function __construct(private array $segments)
    {
    }

    public static function current(): self
    {
        $uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

        $segments = array_values(array_filter(
            explode('/', $uri),
            static fn (string $s): bool => $s !== '',
        ));

        return new self($segments);
    }

    /**
     * Construye una Route sintética con un único segmento.
     * Útil cuando se quiere redirigir a una página sin que la URL
     * actual la contenga (por ejemplo, editor sin URL explícita).
     */
    public static function fromFirstSegment(string $segment): self
    {
        return new self([$segment]);
    }

    public function first(): ?string
    {
        return $this->segments[0] ?? null;
    }

    public function segment(int $index): ?string
    {
        return $this->segments[$index] ?? null;
    }

    /** @return string[] */
    public function segments(): array
    {
        return $this->segments;
    }

    public function isEmpty(): bool
    {
        return $this->segments === [];
    }
}
