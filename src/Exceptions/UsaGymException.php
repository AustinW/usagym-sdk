<?php

declare(strict_types=1);

namespace AustinW\UsaGym\Exceptions;

use Exception;
use Saloon\Http\Response;
use Throwable;

class UsaGymException extends Exception
{
    /**
     * @param array<string, mixed>|null $data
     */
    public function __construct(
        string $message,
        protected readonly ?Response $response = null,
        protected readonly ?array $data = null,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the Saloon response if available
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * Get the parsed response data if available
     *
     * @return array<string, mixed>|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * Create an exception from a Saloon response
     */
    public static function fromResponse(Response $response): self
    {
        $status = $response->status();

        // A 500 from this API commonly arrives as text/html with an empty body, so
        // json() yields null and there is no message to read. Fall back to naming the
        // status and endpoint, which is the only useful information available.
        // Saloon types json() as always returning an array, so PHPStan reads this guard
        // as redundant. It is not: a live 500 from this API returns an empty text/html
        // body, and json() yields null there. Dropping the check reintroduces a fatal on
        // exactly the response the caller is trying to diagnose.
        /** @phpstan-ignore ternary.alwaysTrue */
        $data = is_array($decoded = $response->json()) ? $decoded : null;
        $endpoint = $response->getPendingRequest()->getUrl();

        $message = $data['message']
            ?? sprintf('USA Gymnastics API returned HTTP %d for %s.', $status, $endpoint);

        return match ($status) {
            401, 403 => new AuthenticationException($message, $response, $data, $status),
            404 => new NotFoundException($message, $response, $data, $status),
            422 => new ValidationException($message, $response, $data, $status),
            429 => new RateLimitException($message, $response, $data, $status),
            default => new ApiException($message, $response, $data, $status),
        };
    }
}
