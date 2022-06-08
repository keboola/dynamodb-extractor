<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor\ReadingAdapter;

use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
use Keboola\DynamoDbExtractor\ScanLimit;
use Keboola\DynamoDbExtractor\UserException;

class ScanReadingAdapter extends AbstractReadingAdapter
{
    public function read(array $params): void
    {
        if (isset($this->exportOptions['index'])) {
            $params['IndexName'] = $this->exportOptions['index'];
        }
        if (isset($this->exportOptions['dateFilter'])) {
            $paramsFromDateFilter = $this->createParamsFromDateFilter($this->exportOptions['dateFilter']);
            $this->consoleOutput->writeln((string) json_encode($paramsFromDateFilter));
            $params = array_merge($params, $paramsFromDateFilter);
        }

        $scanLimit = new ScanLimit(1000, $this->exportOptions['limit'] ?? null);

        $marshaler = new Marshaler();

        try {
            do {
                if (isset($response, $response['LastEvaluatedKey'])) {
                    $params['ExclusiveStartKey'] = $response['LastEvaluatedKey'];
                }
                $params['Limit'] = $scanLimit->getBatchSize();
                $response = $this->dynamoDbClient->scan($params)->toArray();
                $scanLimit->decreaseLimit($response['Count']);

                $this->saveResponseItems($marshaler, (array) $response['Items']);
            } while ($scanLimit->shouldContinue() && isset($response['LastEvaluatedKey']));
        } catch (DynamoDbException $e) {
            if ($e->getStatusCode() !== null && substr((string) $e->getStatusCode(), 0, 1) === '4') {
                throw new UserException((string) $e->getAwsErrorCode());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Creates filtering params from date filter
     */
    private function createParamsFromDateFilter(array $dateFilter): array
    {
        return [
            'FilterExpression' => '#field >= :value',
            'ExpressionAttributeNames' => [
                '#field' => $dateFilter['field'],
            ],
            'ExpressionAttributeValues' => [
                ':value' => [
                    'S' => date($dateFilter['format'], strtotime($dateFilter['value'])),
                ],
            ],
        ];
    }
}
