# DynamoDB Extractor

This Docker application exports data from Amazon DynamoDB to Keboola.

## Configuration

A sample configuration and its description can be found [here](/CONFIG.md).

## Output

After a successful extraction, several CSV files containing exported data will be generated. 
- The first output file is named after the `name` parameter in the export configuration.
- Additional files are named according to the destination parameter in the mapping section.

A manifest file is also created for each export.

## Development

Requirements:

- Docker Engine: `~1.12`
- Docker Compose: `~1.8`

This application is designed to run in a Docker container. To start development, follow these steps:

1. Clone this repository: `git clone git@github.com:keboola/dynamodb-extractor.git`
2. Navigate to the project directory: `cd dynamodb-extractor`
3. Build services: `docker compose build`
4. Run tests: `docker compose run --rm app composer ci`

Once all tests pass successfully, continue with:

1. Run the service: `docker compose run --rm app bash`
2. Create tables/indexes and load sample data: `php tests/fixtures/init.php`
3. Write tests and develop the required code.
4. Run tests: `composer tests`

To simulate a real run:

1. Create a data directory: `mkdir -p data`
2. Follow the configuration sample and create a `config.json` file in the data directory (`data/config.json`).
3. Simulate a real run using the entrypoint command: `php ./src/app.php run ./data`

## License

This project is MIT licensed. See the [LICENSE](./LICENSE) file for details.
