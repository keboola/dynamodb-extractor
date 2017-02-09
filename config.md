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
    - `endpoint`: https://your-dynamodb-instance.com/
    - `accessKeyId`: Access key id
    - `#secretAccessKey`: Secret access key (will be encrypted)
    - `regionName`: Region
    
- `exports`: array of exports
