<?php

namespace Keboola\DynamoDbExtractor;

class ScanLimit
{
    /**
     * The maximum number of items to evaluate (DynamoDB's "Limit" parameter)
     * @var int
     */
    private $batchSize;

    /**
     * Number of documents to retrieve
     * @var int|null
     */
    private $limit;

    public function __construct(int $batchSize, int $limit = null)
    {
        $this->batchSize = $batchSize;
        $this->limit = $limit;
    }

    /**
     * Decrease limit by specified number. Takes no effect when limit is null
     * @param int $value
     */
    public function decreaseLimit(int $value): void
    {
        if ($this->limit !== null) {
            $this->limit = $this->limit - $value;
            if ($this->limit < 0) {
                $this->limit = 0;
            }
        }
    }

    /**
     * Gets batch size
     * @return int
     */
    public function getBatchSize(): int
    {
        if ($this->limit === null) {
            return $this->batchSize;
        } else {
            return $this->limit < $this->batchSize ? $this->limit : $this->batchSize;
        }
    }

    /**
     * Gets if scan should continue or not
     * @return bool
     */
    public function shouldContinue(): bool
    {
        return $this->limit === null || $this->limit > 0;
    }
}
