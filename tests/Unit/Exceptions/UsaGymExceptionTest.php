<?php

declare(strict_types=1);

use AustinW\UsaGym\Exceptions\UsaGymException;
use AustinW\UsaGym\Exceptions\AuthenticationException;
use AustinW\UsaGym\Exceptions\ApiException;
use AustinW\UsaGym\Exceptions\NotFoundException;
use AustinW\UsaGym\Exceptions\RateLimitException;
use AustinW\UsaGym\Exceptions\ValidationException;
use AustinW\UsaGym\UsaGym;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use AustinW\UsaGym\Requests\TestRequest;

describe('UsaGymException', function () {
    describe('instantiation', function () {
        it('can be instantiated with only a message', function () {
            $exception = new UsaGymException('Test error');

            expect($exception->getMessage())->toBe('Test error');
            expect($exception->getCode())->toBe(0);
            expect($exception->getResponse())->toBeNull();
            expect($exception->getData())->toBeNull();
        });

        it('can be instantiated with all parameters', function () {
            $connector = new UsaGym('test-user', 'test-pass');
            $mockClient = new MockClient([
                TestRequest::class => MockResponse::make(['error' => 'test'], 500),
            ]);
            $connector->withMockClient($mockClient);

            $response = $connector->send(new TestRequest());
            $data = ['error' => 'detailed error', 'code' => 'ERR001'];
            $previous = new Exception('Previous exception');

            $exception = new UsaGymException(
                message: 'Test error',
                response: $response,
                data: $data,
                code: 500,
                previous: $previous
            );

            expect($exception->getMessage())->toBe('Test error');
            expect($exception->getCode())->toBe(500);
            expect($exception->getResponse())->toBe($response);
            expect($exception->getData())->toBe($data);
            expect($exception->getPrevious())->toBe($previous);
        });

        it('extends PHP Exception class', function () {
            $exception = new UsaGymException('Test error');

            expect($exception)->toBeInstanceOf(Exception::class);
        });
    });

    describe('getResponse()', function () {
        it('returns null when no response is provided', function () {
            $exception = new UsaGymException('Test error');

            expect($exception->getResponse())->toBeNull();
        });

        it('returns the Saloon response when provided', function () {
            $connector = new UsaGym('test-user', 'test-pass');
            $mockClient = new MockClient([
                TestRequest::class => MockResponse::make(['status' => 'error'], 400),
            ]);
            $connector->withMockClient($mockClient);

            $response = $connector->send(new TestRequest());
            $exception = new UsaGymException('Test error', $response);

            expect($exception->getResponse())->toBe($response);
            expect($exception->getResponse()->status())->toBe(400);
        });
    });

    describe('getData()', function () {
        it('returns null when no data is provided', function () {
            $exception = new UsaGymException('Test error');

            expect($exception->getData())->toBeNull();
        });

        it('returns the data array when provided', function () {
            $data = [
                'error' => 'Something went wrong',
                'details' => ['field' => 'value'],
            ];
            $exception = new UsaGymException('Test error', null, $data);

            expect($exception->getData())->toBe($data);
            expect($exception->getData()['error'])->toBe('Something went wrong');
        });

        it('returns empty array when empty data is provided', function () {
            $exception = new UsaGymException('Test error', null, []);

            expect($exception->getData())->toBe([]);
        });
    });

    describe('fromResponse() factory method', function () {
        beforeEach(function () {
            $this->connector = new UsaGym('test-user', 'test-pass');
        });

        it('creates AuthenticationException for 401 status', function () {
            $mockClient = new MockClient([
                TestRequest::class => MockResponse::make(['message' => 'Unauthorized'], 401),
            ]);
            $this->connector->withMockClient($mockClient);

            $response = $this->connector->send(new TestRequest());
            $exception = UsaGymException::fromResponse($response);

            expect($exception)->toBeInstanceOf(AuthenticationException::class);
            expect($exception->getMessage())->toBe('Unauthorized');
            expect($exception->getCode())->toBe(401);
            expect($exception->getResponse())->toBe($response);
            expect($exception->getData())->toBe(['message' => 'Unauthorized']);
        });

        it('creates AuthenticationException for 403 status', function () {
            $mockClient = new MockClient([
                TestRequest::class => MockResponse::make(['message' => 'Forbidden'], 403),
            ]);
            $this->connector->withMockClient($mockClient);

            $response = $this->connector->send(new TestRequest());
            $exception = UsaGymException::fromResponse($response);

            expect($exception)->toBeInstanceOf(AuthenticationException::class);
            expect($exception->getMessage())->toBe('Forbidden');
            expect($exception->getCode())->toBe(403);
        });

        it('creates NotFoundException for 404 status', function () {
            $mockClient = new MockClient([
                TestRequest::class => MockResponse::make(['message' => 'Resource not found'], 404),
            ]);
            $this->connector->withMockClient($mockClient);

            $response = $this->connector->send(new TestRequest());
            $exception = UsaGymException::fromResponse($response);

            expect($exception)->toBeInstanceOf(NotFoundException::class);
            expect($exception->getMessage())->toBe('Resource not found');
            expect($exception->getCode())->toBe(404);
        });

        it('creates ValidationException for 422 status', function () {
            $mockClient = new MockClient([
                TestRequest::class => MockResponse::make([
                    'message' => 'Validation failed',
                    'errors' => ['email' => ['The email field is required.']],
                ], 422),
            ]);
            $this->connector->withMockClient($mockClient);

            $response = $this->connector->send(new TestRequest());
            $exception = UsaGymException::fromResponse($response);

            expect($exception)->toBeInstanceOf(ValidationException::class);
            expect($exception->getMessage())->toBe('Validation failed');
            expect($exception->getCode())->toBe(422);
        });

        it('creates RateLimitException for 429 status', function () {
            $mockClient = new MockClient([
                TestRequest::class => MockResponse::make(['message' => 'Too many requests'], 429),
            ]);
            $this->connector->withMockClient($mockClient);

            $response = $this->connector->send(new TestRequest());
            $exception = UsaGymException::fromResponse($response);

            expect($exception)->toBeInstanceOf(RateLimitException::class);
            expect($exception->getMessage())->toBe('Too many requests');
            expect($exception->getCode())->toBe(429);
        });

        it('creates ApiException for other status codes', function () {
            $mockClient = new MockClient([
                TestRequest::class => MockResponse::make(['message' => 'Internal server error'], 500),
            ]);
            $this->connector->withMockClient($mockClient);

            $response = $this->connector->send(new TestRequest());
            $exception = UsaGymException::fromResponse($response);

            expect($exception)->toBeInstanceOf(ApiException::class);
            expect($exception->getMessage())->toBe('Internal server error');
            expect($exception->getCode())->toBe(500);
        });

        it('creates ApiException for 400 Bad Request', function () {
            $mockClient = new MockClient([
                TestRequest::class => MockResponse::make(['message' => 'Bad request'], 400),
            ]);
            $this->connector->withMockClient($mockClient);

            $response = $this->connector->send(new TestRequest());
            $exception = UsaGymException::fromResponse($response);

            expect($exception)->toBeInstanceOf(ApiException::class);
            expect($exception->getMessage())->toBe('Bad request');
            expect($exception->getCode())->toBe(400);
        });

        it('creates ApiException for 502 Bad Gateway', function () {
            $mockClient = new MockClient([
                TestRequest::class => MockResponse::make(['message' => 'Bad gateway'], 502),
            ]);
            $this->connector->withMockClient($mockClient);

            $response = $this->connector->send(new TestRequest());
            $exception = UsaGymException::fromResponse($response);

            expect($exception)->toBeInstanceOf(ApiException::class);
            expect($exception->getCode())->toBe(502);
        });

        it('creates ApiException for 503 Service Unavailable', function () {
            $mockClient = new MockClient([
                TestRequest::class => MockResponse::make(['message' => 'Service unavailable'], 503),
            ]);
            $this->connector->withMockClient($mockClient);

            $response = $this->connector->send(new TestRequest());
            $exception = UsaGymException::fromResponse($response);

            expect($exception)->toBeInstanceOf(ApiException::class);
            expect($exception->getCode())->toBe(503);
        });

        // The former default was the bare string "An API error occurred", which told a
        // caller nothing about what failed. Naming the status and endpoint is the point:
        // a 500 with an empty body has no message field to read, and that is exactly the
        // case where the caller most needs to know what happened.
        it('falls back to naming the status and endpoint when the body has no message', function () {
            $mockClient = new MockClient([
                TestRequest::class => MockResponse::make(['error' => 'Something went wrong'], 500),
            ]);
            $this->connector->withMockClient($mockClient);

            $response = $this->connector->send(new TestRequest());
            $exception = UsaGymException::fromResponse($response);

            expect($exception->getMessage())
                ->toContain('HTTP 500')
                ->toContain('USA Gymnastics API');
        });

        it('still reports a status when the body is empty and not JSON at all', function () {
            $mockClient = new MockClient([
                TestRequest::class => MockResponse::make('', 500, ['Content-Type' => 'text/html']),
            ]);
            $this->connector->withMockClient($mockClient);

            $response = $this->connector->send(new TestRequest());
            $exception = UsaGymException::fromResponse($response);

            expect($exception)->toBeInstanceOf(AustinW\UsaGym\Exceptions\ApiException::class)
                ->and($exception->getMessage())->toContain('HTTP 500')
                ->and($exception->getCode())->toBe(500);
        });

        it('preserves response data in exception', function () {
            $responseData = [
                'message' => 'Error occurred',
                'error_code' => 'ERR_001',
                'details' => ['additional' => 'info'],
            ];
            $mockClient = new MockClient([
                TestRequest::class => MockResponse::make($responseData, 500),
            ]);
            $this->connector->withMockClient($mockClient);

            $response = $this->connector->send(new TestRequest());
            $exception = UsaGymException::fromResponse($response);

            expect($exception->getData())->toBe($responseData);
            expect($exception->getData()['error_code'])->toBe('ERR_001');
        });
    });
});
