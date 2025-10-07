<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    protected $productId;

    /**
     * Constructor.
     *
     * @param string $message
     * @param int $productId
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message = "", $productId = 0, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->productId = $productId;
    }

    /**
     * Get the product ID associated with the exception.
     *
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }
}