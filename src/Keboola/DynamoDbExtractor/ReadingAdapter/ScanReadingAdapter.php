<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor\ReadingAdapter;

use Aws\DynamoDb\Marshaler;
use Keboola\DynamoDbExtractor\ReadingLimit;

class ScanReadingAdapter extends AbstractReadingAdapter
{
    public function read(array $params): void
    {
        if (isset($this->exportOptions['index'])) {
            $params['IndexName'] = $this->exportOptions['index'];
        }
        if (isset($this->exportOptions['dateFilter'])) {
            $paramsFromDateFilter = $this->createParamsFromDateFilter($this->exportOptions['dateFilter']);
            $this->consoleOutput->writeln((string) json_encode($paramsFromDateFilter, JSON_THROW_ON_ERROR));
            $params = array_merge($params, $paramsFromDateFilter);
        }

        $scanLimit = new ReadingLimit(1000, $this->exportOptions['limit'] ?? null);

        $marshaler = new Marshaler();

        do {
            if (isset($response, $response['LastEvaluatedKey'])) {
                $params['ExclusiveStartKey'] = $response['LastEvaluatedKey'];
            }
            $params['Limit'] = $scanLimit->getBatchSize();
            $response = $this->dynamoDbClient->scan($params)->toArray();
            $scanLimit->decreaseLimit($response['Count']);

            $this->saveResponseItems($marshaler, (array) $response['Items']);
        } while ($scanLimit->shouldContinue() && isset($response['LastEvaluatedKey']));
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
