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

        if (!empty($this->exportOptions['indexName'])) {
            $params['IndexName'] = $this->exportOptions['indexName'];
        }
        if (!empty($this->exportOptions['keyConditionExpression'])) {
            $params['KeyConditionExpression'] = $this->exportOptions['keyConditionExpression'];
        }
        if (!empty($this->exportOptions['expressionAttributeValues'])) {
            $params['ExpressionAttributeValues'] = $marshaler->marshalJson(
                (string) json_encode($this->exportOptions['expressionAttributeValues'])
            );
        }
        if (!empty($this->exportOptions['expressionAttributeNames'])) {
            $params['ExpressionAttributeNames'] = $this->exportOptions['expressionAttributeNames'];
        }

        $scanLimit = new ReadingLimit(1000, $this->exportOptions['limit'] ?? null);

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
}
