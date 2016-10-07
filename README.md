# DynamoDB Extractor

[![Build Status](https://travis-ci.org/keboola/dynamodb-extractor.svg?branch=master)](https://travis-ci.org/keboola/dynamodb-extractor)
[![Code Climate](https://codeclimate.com/github/keboola/dynamodb-extractor/badges/gpa.svg)](https://codeclimate.com/github/keboola/dynamodb-extractor)
[![Test Coverage](https://codeclimate.com/github/keboola/dynamodb-extractor/badges/coverage.svg)](https://codeclimate.com/github/keboola/dynamodb-extractor/coverage)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/keboola/dynamodb-extractor/blob/master/LICENSE.md)

Docker application for exporting data from Amazon DynamoDB.

## Configuration

```json
{
  "parameters": {
    "db": {
      "endpoint": "endpoint",
      "accessKeyId": "access key id",
      "#secretAccessKey": "secret access key",
      "regionName": "eu-central-1"
    },
    "exports": []
  }
}
```

## License

MIT. See license file.
