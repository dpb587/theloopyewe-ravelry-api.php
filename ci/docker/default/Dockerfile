FROM ubuntu:15.10
RUN apt-get update \
  && apt-get install -y curl git php5-cli \
  && curl -L https://github.com/stedolan/jq/releases/download/jq-1.5/jq-linux64 > /usr/bin/jq && chmod +x /usr/bin/jq \
  && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
