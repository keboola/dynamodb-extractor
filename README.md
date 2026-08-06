# DynamoDB Extractor

This Docker application exports data from Amazon DynamoDB to Keboola.

## Configuration

A sample configuration and its description can be found [here](/CONFIG.md).

The JSON schema backing the configuration form (and the validation used by the Keboola MCP
server and the in-platform AI assistant) lives in
[`component_config/configSchema.json`](/component_config/configSchema.json). It describes the
contents of `parameters` only.

## Developer Portal

`scripts/developer_portal/update_properties.sh` pushes the repo-owned Developer Portal
properties. It runs automatically from the `deploy` job on a semantic-tag release, so the
repository — not the portal — is the source of truth for whatever it lists.

Today it pushes **only** `configurationSchema`, from `component_config/configSchema.json`.
Every other portal property (descriptions, URLs, `actions`, `uiOptions`, `encryption`, …) is
left untouched and stays portal-owned.

Two things to know before changing it:

- Editing `component_config/configSchema.json` is the durable way to change the live schema. A
  manual portal patch is overwritten on the next tagged release.
- Adding a property to the script means the repo file starts overwriting the live value. Diff
  the repo file against `kbagent dev-portal get --app keboola.ex-dynamodb` first. The script
  refuses to push an empty `{}` / `[]` document rather than silently clearing a live property.

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
