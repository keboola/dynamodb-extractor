# Configuration

## Sample

```json
{
  "parameters": {
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
}
```

## Description of `parameters`

- `db`: DynamoDB instance connection options
    - `endpoint`: `https://dynamodb.REGION.amazonaws.com`
    - `accessKeyId`: Access key id
    - `#secretAccessKey`: Secret access key (will be encrypted)
    - `regionName`: Region
    
- `exports`: array of exports
    - `id`: unique numeric identifier of export
    - `name`: unique string identifier of export (base table will be named after it)
    - `table`: name of the table to export from
    - `index`: (optional) name of the index to export from
    - `enabled` (optional, default: `true`): if export is enabled or not (there has to be at least one enabled export)
    - `incremental`: if load of tables to storage will be incremental
    - `dateFilter` (optional): how to filter scanned documents
        - `field`: field name in document by which you want to filter
        - `format`: date format (e.g. `Y-m-d` for date or `Y` for year)
        - `value`: date string from which date value will be created (e.g. `-2 days`)
    - `limit` (optional): how many documents you want to export
    - `mapping`: how to map fields in document to CSV columns


### `dateFilter` and its fields

You can specify `dateFilter` parameter to filter documents you want export. Filter condition is
composed from 3 fields: `field`, `format` and `value`.

The `value` field is passed to *strtotime* function. Then the `format` and `value` fields are passed
to *date* function to create final value which will be used to filter documents. Something like
`date($format, strtotime($value))`.

#### Example

(for date `2018-03-13 19:00:00`)

|field|format|value|composed condition|
|---|---|---|---|
|`createdTime`|`Y`|`today`|`createdTime >= 2018`|
|`createdTime`|`Y-m-d`|`-2 days`|`createdTime >= 2018-03-11`|
|`createdTime`|`Y-m-d H:i:s`|`-10 hours`|`createdTime >= 2018-03-13 08:00:00`|
|`createdTime`|`Y-m-d`|`2018-01-01`|`createdTime >= 2018-01-01`|

### `mapping` and its fields

tbd

## General filtering options

tbd

## Links