<?php

namespace AustinW\Usagym;

use AustinW\Usagym\Actions\Reservations;
use AustinW\Usagym\Resources\User;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Http;

class UsagymApi
{
    use MakesHttpRequests;
    use Reservations;

    /**
     * The Forge API Key.
     */
    protected string $apiKey;

    /**
     * The Guzzle HTTP Client instance.
     *
     * @var \GuzzleHttp\Client
     */
    public $guzzle;

    /**
     * Number of seconds a request is retried.
     */
    public int $timeout = 30;

    /**
     * Create a new Forge instance.
     *
     * @return void
     */
    public function __construct(?string $apiKey = null, ?HttpClient $guzzle = null)
    {
        if (! is_null($guzzle)) {
            $this->guzzle = $guzzle;
        } else {
            $this->guzzle = new HttpClient();
        }

        if (! is_null($apiKey)) {
            $this->setApiKey($apiKey, $guzzle);
        }

    }

    /**
     * Transform the items of the collection to the given class.
     *
     * @param  array  $collection
     * @param  string  $class
     * @param  array  $extraData
     * @return array
     */
    protected function transformCollection($collection, $class, $extraData = [])
    {
        return array_map(function ($data) use ($class, $extraData) {
            return new $class($data + $extraData, $this);
        }, $collection);
    }

    /**
     * Set the api key and setup the guzzle request object.
     *
     * @param  string  $apiKey
     * @param  \GuzzleHttp\Client|null  $guzzle
     * @return $this
     */
    public function setApiKey($apiKey, $guzzle = null)
    {
        $this->apiKey = $apiKey;

        $this->guzzle = $guzzle ?: new HttpClient([
            'base_uri' => 'https://api.usagym.org/v4/',
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Basic '.$this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        return $this;
    }

    /**
     * Set a new timeout.
     *
     * @param  int  $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Get the timeout.
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Get an authenticated user instance.
     *
     * @return \AustinW\Usagym\Resources\User
     */
    public function user()
    {
        return new User($this->get('user')['user']);
    }
}
