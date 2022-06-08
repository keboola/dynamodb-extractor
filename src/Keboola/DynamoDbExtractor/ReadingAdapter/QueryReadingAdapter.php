<?php

declare(strict_types=1);

namespace Keboola\DynamoDbExtractor\ReadingAdapter;

use Aws\DynamoDb\Marshaler;

class QueryReadingAdapter extends AbstractReadingAdapter
{
    public function read(array $params): void
    {
        $marshaler = new Marshaler();

        $params['KeyConditionExpression'] = $this->exportOptions['keyConditionExpression'];
        $params['ExpressionAttributeValues'] = $marshaler->marshalJson(
            (string) json_encode($this->exportOptions['expressionAttributeValues'])
        );
        if (!empty($this->exportOptions['expressionAttributeNames'])) {
            $params['ExpressionAttributeNames'] = $this->exportOptions['expressionAttributeNames'];
        }
        $response = $this->dynamoDbClient->query($params);

        $this->saveResponseItems($marshaler, (array) $response['Items']);
    }
}
