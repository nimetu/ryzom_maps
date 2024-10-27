#!/bin/sh

if [ "${1:-}" = "--build" ]; then
	docker build --rm -t jest - < Dockerfile
	exit
fi

docker run --rm -v ".:/home/node/app" -u $(id -u):$(id -g) -it jest npx jest

