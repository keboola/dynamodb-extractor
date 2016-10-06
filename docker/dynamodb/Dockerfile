FROM openjdk:7

RUN apt-get update -q \
  && apt-get install wget -y --no-install-recommends

RUN mkdir -p /opt/dynamodb
WORKDIR /opt/dynamodb

RUN wget http://dynamodb-local.s3-website-us-west-2.amazonaws.com/dynamodb_local_latest.tar.gz \
  && tar -xvzf dynamodb_local_latest.tar.gz

CMD java -Djava.library.path=./DynamoDBLocal_lib -jar DynamoDBLocal.jar -inMemory
