<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\SharQ;

class Config
{
    public const CLIENT_MYSQL = 'mysql';

    public const CLIENTS =
    [
        self::CLIENT_MYSQL,
    ];

    private string $client;

    private string $host;
    private int $port = 3306;
    private string $user;
    private string $password;
    private string $database;
    private string $charset;

    private int $timeout = 10000;

    public function __construct(string $client)
    {// 2023-05-08
        if (!in_array($client, self::CLIENTS))
        {
            throw new \Exception('Invalid client: '.$client);
        }

        $this->client = $client;
    }

    public function host(string $host): self
    {// 2023-05-08
        $this->host = $host;

        return $this;
    }

    public function port(int $port): self
    {// 2023-05-08
        $this->port = $port;

        return $this;
    }

    public function user(string $user): self
    {// 2023-05-08
        $this->user = $user;

        return $this;
    }

    public function password(string $password): self
    {// 2023-05-08
        $this->password = $password;

        return $this;
    }

    public function database(string $database): self
    {// 2023-05-08
        $this->database = $database;

        return $this;
    }

    public function charset(string $charset): self
    {// 2023-05-08
        $this->charset = $charset;

        return $this;
    }

    /**
     * 2023-05-08
     * How long to wait for a connection
     * @param int $milliSeconds
     * @throws Exception\QueryTimeoutException
     * @return self
     */
    public function timeout(int $milliSeconds): self
    {// 2023-05-08
        if ($milliSeconds < 0)
        {
            throw new \UnexpectedValueException('Timeout must be a positive integer');
        }

        $this->timeout = $milliSeconds;

        return $this;
    }

    public function getClient(): string
    {// 2023-05-08
        return $this->client;
    }

    public function getHost(): string
    {// 2023-05-08
        return $this->host;
    }

    public function getPort(): int
    {// 2023-05-08
        return $this->port;
    }

    public function getUser(): string
    {// 2023-05-08
        return $this->user;
    }

    public function getPassword(): string
    {// 2023-05-08
        return $this->password;
    }

    public function getDatabase(): string
    {// 2023-05-08
        return $this->database;
    }

    public function getCharset(): string
    {// 2023-05-08
        return $this->charset;
    }

    public function getTimeout(): int
    {// 2023-05-08
        return $this->timeout;
    }
}
