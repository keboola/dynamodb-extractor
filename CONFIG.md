# Configuring in Keboola UI

## Sample Scan Mode


```json
{
  "db": {
    "endpoint": "endpoint",
    "accessKeyId": "access key id",
    "#secretAccessKey": "secret access key",
    "regionName": "eu-central-1"
  },
  "exports": [
    {
      "id": 1,
      "name": "my-movies",
      "table": "Movies",
      "index": "Movies_SomeIndex",
      "enabled": true,
      "incremental": true,
      "primaryKey": [
        "title",
        "year"
      ],
      "mode": "scan",
      "dateFilter": {
        "field": "year",
        "format": "Y",
        "value": "2014-01-01"
      },
      "limit": 100,
      "mapping": {
        "title": "title",
        "year": "year",
        "info.rating": "rating"
      }
    }
  ]
}
```


## Sample Query Mode


```json
{
  "db": {
    "endpoint": "endpoint",
    "accessKeyId": "access key id",
    "#secretAccessKey": "secret access key",
    "regionName": "eu-central-1"
  },
  "exports": [
    {
      "id": 1,
      "name": "my-movies",
      "table": "Movies",
      "index": "Movies_SomeIndex",
      "enabled": true,
      "incremental": true,
      "primaryKey": [
        "title",
        "year"
      ],
      "mode": "query",
      "keyConditionExpression": "$yr = :a",
      "expressionAttributeNames": {
        "$yr": "year"
      },
      "expressionAttributeValues": {
        ":a": "2013"
      },
      "limit": 100,
      "mapping": {
        "title": "title",
        "year": "year",
        "info.rating": "rating"
      }
    }
  ]
}
```


## Sample Query Mode (with Secondary Index)


```json
{
  "db": {
    "endpoint": "endpoint",
    "accessKeyId": "access key id",
    "#secretAccessKey": "secret access key",
    "regionName": "eu-central-1"
  },
  "exports": [
    {
      "id": 1,
      "name": "my-movies",
      "table": "Movies",
      "enabled": true,
      "incremental": true,
      "primaryKey": [
        "title",
        "year"
      ],
      "mode": "query",
      "indexName": "Movies_title",
      "keyConditionExpression": "title = :a",
      "expressionAttributeValues": {
        ":a": "Rush"
      },
      "limit": 100,
      "mapping": {
        "title": "title",
        "year": "year",
        "info.rating": "rating"
      }
    }
  ]
}
```

## Description of `parameters`

- `db`: DynamoDB instance connection options
    - `endpoint`: `https://dynamodb.REGION.amazonaws.com`
    - `accessKeyId`: Access key ID
    - `#secretAccessKey`: Secret access key (will be encrypted)
    - `regionName`: Region
    
- `exports`: An array of export configurations.
    - `id`: A unique numeric identifier for the export.
    - `name`: A unique string identifier for the export (the base table will be named after it).
    - `table`: The name of the table to export from.
    - `index` (optional): The name of the index to export from.
    - `enabled` (optional, default: `true`): Specifies whether the export is enabled. (At least one export must be enabled.)
    - `incremental`: Determines whether tables loads to Storage will be incremental.
    - `primaryKey`: The primary key to set on the imported table, defined as an array.
    - `mode` (optional): enum(scan|query): The reading mode from DynamoDB. Default: `scan`.
    - `keyConditionExpression` (required): Specifies a specific value for the partition key.
    - `expressionAttributeValues` (required): Defines values that can be substituted in an expression.
    - `expressionAttributeNames` (optional): Substitution tokens for attribute names in an expression. You can use the placeholder `$` instead of `#` (see sample).
    - `dateFilter` (optional): Defines how to filter scanned documents. *(Applicable only for scan mode.)*
        - `field`: The document field name used for filtering.
        - `format`: The date format (e.g., `Y-m-d` for a full date or `Y` for a year).
        - `value`: The relative date string from which date value will be calculated (e.g., `-2 days`).
    - `limit` (optional): Specifies how many documents to export.
    - `mapping`: Defines how document fields should be mapped to CSV columns.


### `dateFilter`

***Note:** To use `dateFilter` and incremental loads, make sure that your database (or index)
contains a field that can be used for filtering documents. For example, add a `creationDate` field to every document you create.*

The extractor uses the `Scan` operation to select documents from DynamoDB.

You can specify the `dateFilter` parameter to filter the documents you want export. The filter condition is
composed of three fields: `field`, `format`, and `value`.

The `value` field is passed to the [**strtotime**](https://secure.php.net/strtotime) function. Then, the
`format` and `value` fields are passed to the [**date**](https://secure.php.net/date) function to generate the
final value, which will be used to filter documents. The equivalent function call is: `date($format, strtotime($value))`.

#### Example

(For date `2018-03-13 18:00:00`)

|field|format|value|composed condition|
|---|---|---|---|
|`createdTime`|`Y`|`today`|`createdTime >= 2018`|
|`createdTime`|`Y-m-d`|`-2 days`|`createdTime >= 2018-03-11`|
|`createdTime`|`Y-m-d H:i:s`|`-10 hours`|`createdTime >= 2018-03-13 08:00:00`|
|`createdTime`|`Y-m-d`|`2018-01-01`|`createdTime >= 2018-01-01`|

### `mapping`

- The [php-csvmap](https://github.com/keboola/php-csvmap) library is used to parse exported documents.
- In most cases, a simple mapping like `"some.path.key": "destination"` will suffice.
- For advanced use cases, please refer mapping sections of in:
    - [MongoDB Extractor](https://help.keboola.com/extractors/database/mongodb/mapping/)
    - [Generic Extractor](https://developers.keboola.com/extend/generic-extractor/configuration/config/mappings/) 

## Links

- [Scanning a Table](https://docs.aws.amazon.com/amazondynamodb/latest/developerguide/SQLtoNoSQL.ReadData.Scan.html)
- [DynamoDB Scan Operation](https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_Scan.html)
