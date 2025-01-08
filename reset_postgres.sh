#!/usr/bin/env bash
podman run -d --replace -e POSTGRES_PASSWORD=epic_password -e POSTGRES_USERNAME=postgres -e POSTGRES_DB=private_events -p 5432:5432 -v pgdata:/var/lib/pgsql/data --name=postgres postgres:16
