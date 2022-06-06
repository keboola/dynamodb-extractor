<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor;

class ScanLimit
{
    /** The maximum number of items to evaluate (DynamoDB's "Limit" parameter) */
    private int $batchSize;

    /** Number of documents to retrieve */
    private ?int $limit;

    public function __construct(int $batchSize, ?int $limit = null)
    {
        $this->batchSize = $batchSize;
        $this->limit = $limit;
    }

    /** Decrease limit by specified number. Takes no effect when limit is null */
    public function decreaseLimit(int $value): void
    {
        if ($this->limit !== null) {
            $this->limit = $this->limit - $value;
            if ($this->limit < 0) {
                $this->limit = 0;
            }
        }
    }

    /** Gets batch size */
    public function getBatchSize(): int
    {
        if ($this->limit === null) {
            return $this->batchSize;
        } else {
            return $this->limit < $this->batchSize ? $this->limit : $this->batchSize;
        }
    }

    /** Gets if scan should continue or not */
    public function shouldContinue(): bool
    {
        return $this->limit === null || $this->limit > 0;
    }
}
