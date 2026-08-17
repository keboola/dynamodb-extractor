#!/usr/bin/env bash

set -e

# Pushes repo-owned Developer Portal properties on a semantic-tag deploy, so the
# repository stays the source of truth for them.
#
# Only the properties listed at the bottom of this file are pushed. Everything
# else on the app (descriptions, URLs, actions, uiOptions, encryption, ...) is
# left alone and remains portal-owned. Before adding a property here, diff the
# repo file against `kbagent dev-portal get --app "$KBC_DEVELOPERPORTAL_APP"` —
# adding one starts overwriting whatever is live.

if [ -z "$KBC_DEVELOPERPORTAL_VENDOR" ] || [ -z "$KBC_DEVELOPERPORTAL_APP" ]; then
    echo "Error: KBC_DEVELOPERPORTAL_VENDOR and KBC_DEVELOPERPORTAL_APP must be set."
    exit 1
fi

docker pull quay.io/keboola/developer-portal-cli-v2:latest

update_property() {
    local prop_name="$1"
    local file_path="$2"

    if [ ! -f "$file_path" ]; then
        echo "File '$file_path' not found. Skipping '$prop_name'."
        return
    fi

    local value
    value=$(<"$file_path")

    # An "empty" JSON document is not neutral. The portal accepts `{}` happily and
    # the live value is replaced by nothing, so a placeholder file silently wipes a
    # populated property on the next release. Fail loudly instead.
    local compact
    compact=$(echo "$value" | tr -d '[:space:]')
    if [ -z "$compact" ] || [ "$compact" = "{}" ] || [ "$compact" = "[]" ]; then
        echo "Error: '$file_path' is empty ('$compact'). Refusing to overwrite '$prop_name'."
        echo "Delete the file if the property should stay portal-owned."
        exit 1
    fi

    echo "Updating $prop_name for $KBC_DEVELOPERPORTAL_APP from $file_path (${#value} bytes)"

    docker run --rm \
        -e KBC_DEVELOPERPORTAL_USERNAME \
        -e KBC_DEVELOPERPORTAL_PASSWORD \
        quay.io/keboola/developer-portal-cli-v2:latest \
        update-app-property \
        "$KBC_DEVELOPERPORTAL_VENDOR" "$KBC_DEVELOPERPORTAL_APP" "$prop_name" --value="$value"

    echo "Property $prop_name updated successfully."
}

update_property "configurationSchema" "component_config/configSchema.json"
