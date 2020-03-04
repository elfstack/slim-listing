#! /bin/bash

docker run --rm --interactive --tty \
  --volume $PWD:/app \
  composer:latest \
  /bin/sh
