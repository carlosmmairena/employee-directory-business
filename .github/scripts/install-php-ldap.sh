#!/usr/bin/env bash
# Install the PHP LDAP extension in all running wp-env Docker containers
# so that plugin-check-action can activate the plugin successfully.
set -euo pipefail

CMD='apt-get update -qq && apt-get install -y -qq libldap2-dev && docker-php-ext-install ldap'

for container in $(docker ps --filter name=wordpress- --format '{{.Names}}'); do
	echo "Installing php-ldap in container: $container"
	docker exec "$container" bash -c "$CMD" \
		&& echo "  Done: $container" \
		|| echo "  Warning: could not install php-ldap in $container"
done
