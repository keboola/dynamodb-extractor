# DynamoDB Extractor

Docker application for exporting data from Amazon DynamoDB.

## Configuration

Sample configuration and its description can be found [here](/CONFIG.md).

## Output

After successful extraction there are several CSV files which contains exported data. First output
file is named after `name` parameter in export configuration. Other files are named after destination
parameter in mapping section.

Also, there is manifest file for each of the export.

## Development

Requirements:

- Docker Engine: `~1.12`
- Docker Compose: `~1.8`

Application is prepared for run in container, you can start development same way:

1. Clone this repository: `git clone git@github.com:keboola/dynamodb-extractor.git`
2. Change directory: `cd dynamodb-extractor`
3. Build services: `docker-compose build`
4. Run tests `docker-compose run --rm app-tests` (runs `./tests.sh` script)

After seeing all tests green, continue:

1. Run service: `docker-compose run --rm app` (starts container with `bash`)
2. Create tables/indexes and load sample data: `php init.php`
3. Write tests and code
4. Run tests: `./tests.sh`

To simulate real run:

1. Create data dir: `mkdir -p data`
2. Follow configuration sample and create `config.json` file and place it to your data directory (`data/config.json`)
3. Simulate real run (with entrypoint command): `php ./src/app.php run ./data`

### Tests

- all in one: `./tests.sh`
- or separately, just check `tests.sh` file contents

## License

MIT licensed, see [LICENSE](./LICENSE) file.
