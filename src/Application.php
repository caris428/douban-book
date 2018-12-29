<?php

/*
 * This file is part of the littlesqx/douban-book.
 *
 * (c) littlesqx <littlesqx@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Littlesqx\Book;

use GuzzleHttp\Client;
use Littlesqx\Book\Entities\Book;
use Littlesqx\Book\Exceptions\HttpException;
use Littlesqx\Book\Exceptions\InvalidArgumentException;
use Littlesqx\Book\Exceptions\InvalidResponseException;

class Application
{
    /**
     * @var array http client configuration
     */
    protected $httpOptions = [];

    /**
     * @var string request api url, via douban/v2
     */
    protected $requestUrl = 'https://api.douban.com/v2/book/';

    /**
     * set http client options.
     *
     * @param array $httpOptions
     *
     * @return $this
     */
    public function setHttpOptions(array $httpOptions)
    {
        $this->httpOptions = $httpOptions;

        return $this;
    }

    /**
     * get http client options.
     *
     * @return array
     */
    public function getHttpOptions(): array
    {
        return $this->httpOptions;
    }

    /**
     * get a http client.
     *
     * @return Client
     */
    public function getHttpClient(): Client
    {
        return new Client($this->httpOptions);
    }

    /**
     * get a book by isbn code.
     *
     * @param string $isbn
     *
     * @return Book
     *
     * @throws HttpException
     * @throws InvalidArgumentException
     * @throws InvalidResponseException
     */
    public function getBook(string $isbn): Book
    {
        if (13 !== strlen($isbn) && 10 !== strlen($isbn)) {
            throw new InvalidArgumentException('Invalid isbn code(isbn10 or isbn13): '.$isbn);
        }
        $queryParams = ['isbn' => $isbn];

        try {
            $response = $this->getHttpClient()->get($this->requestUrl.array_to_path($queryParams));
            if (200 === $response->getStatusCode()) {
                return BookFactory::make($response->getBody()->getContents());
            }
        } catch (\Exception $e) {
            if ($e instanceof InvalidResponseException) {
                throw $e;
            }

            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
