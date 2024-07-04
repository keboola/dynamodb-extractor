<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor\ReadingAdapter;

use Aws\DynamoDb\Marshaler;
use Keboola\DynamoDbExtractor\ReadingLimit;

class QueryReadingAdapter extends AbstractReadingAdapter
{
    public function read(array $params): void
    {
        $marshaler = new Marshaler();

        $options = $this->replaceAttributeNamesPlaceholders($this->exportOptions);

        if (!empty($options['indexName'])) {
            $params['IndexName'] = $options['indexName'];
        }

        if (!empty($options['keyConditionExpression'])) {
            $params['KeyConditionExpression'] = $options['keyConditionExpression'];
        }

        if (!empty($options['expressionAttributeValues'])) {
            $params['ExpressionAttributeValues'] = $marshaler->marshalJson(
                (string) json_encode($options['expressionAttributeValues']),
            );
        }

        if (!empty($options['expressionAttributeNames'])) {
            $params['ExpressionAttributeNames'] = $options['expressionAttributeNames'];
        }

        $scanLimit = new ReadingLimit(1000, $options['limit'] ?? null);

        do {
            if (isset($response, $response['LastEvaluatedKey'])) {
                $params['ExclusiveStartKey'] = $response['LastEvaluatedKey'];
            }
            $params['Limit'] = $scanLimit->getBatchSize();
            $response = $this->dynamoDbClient->query($params)->toArray();
            $scanLimit->decreaseLimit($response['Count']);

            $this->saveResponseItems($marshaler, (array) $response['Items']);
        } while ($scanLimit->shouldContinue() && isset($response['LastEvaluatedKey']));
    }

    private function replaceAttributeNamesPlaceholders(array $exportOptions): array
    {
        if (empty($exportOptions['expressionAttributeNames']) || empty($exportOptions['keyConditionExpression'])) {
            return $exportOptions;
        }
        $placeholdes = [];
        $attributeNames = [];
        foreach ($exportOptions['expressionAttributeNames'] as $key => $value) {
            $name = $key;
            if (mb_substr($key, 0, 1) === '$') {
                $name = sprintf('#%s', mb_substr($key, 1));
                $placeholdes[$key] = $name;
            }
            $attributeNames[$name] = $value;
        }

        $exportOptions['expressionAttributeNames'] = $attributeNames;
        $exportOptions['keyConditionExpression'] = str_ireplace(
            array_keys($placeholdes),
            array_values($placeholdes),
            $exportOptions['keyConditionExpression'],
        );

        return $exportOptions;
    }
}
