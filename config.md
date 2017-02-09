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
    - `endpoint`: `https://your-dynamodb-instance.com/`
    - `accessKeyId`: Access key id
    - `#secretAccessKey`: Secret access key (will be encrypted)
    - `regionName`: Region
    
- `exports`: array of exports
    - `id`: unique numeric identifier of export
    - `name`: unique string identifier of export (base table will be named after it)
    - `table`: name of the table to export from
    - `enabled` (optional, default: `true`): if export is enabled or not (there has to be aty least one enabled export)
    - `incremental`: if load of tables to storage will be incremental
    - `dateFilter` (optional): how to filter scanned documents
        - `field`: field name in document by which you want to filter
        - `format`: date format (e.g. `Y-m-d` for date or `Y` for year)
        - `value`: date string from which date value will be created (e.g. `-2 days`)
    - `limit` (optional): how many documents you want to export
    - `mapping`: how to map fields in document to CSV columns


### `dateFilter` and its fields

tbd

### `mapping` and its fields

tbd

## General filtering options

tbd

## Links
