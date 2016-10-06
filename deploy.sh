#!/bin/bash

docker login -u="$QUAY_USERNAME" -p="$QUAY_PASSWORD" quay.io
docker tag keboola/dynamodb-extractor quay.io/keboola/dynamodb-extractor:$TRAVIS_TAG
docker images
docker push quay.io/keboola/dynamodb-extractor:$TRAVIS_TAG
