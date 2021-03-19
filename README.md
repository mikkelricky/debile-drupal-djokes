# Debile Djokes

## Installation

Create local settings file with database connection:

```sh
cat <<'EOF' > web/sites/default/settings.local.php
<?php
$databases['default']['default'] = [
 'database' => getenv('DATABASE_DATABASE') ?: 'db',
 'username' => getenv('DATABASE_USERNAME') ?: 'db',
 'password' => getenv('DATABASE_PASSWORD') ?: 'db',
 'host' => getenv('DATABASE_HOST') ?: 'mariadb',
 'port' => getenv('DATABASE_PORT') ?: '',
 'driver' => getenv('DATABASE_DRIVER') ?: 'mysql',
 'prefix' => '',
];
EOF
```

Install site:

```sh
composer install --no-dev --optimize-autoloader
vendor/bin/drush --yes site:install minimal --existing-config
```

## Updates

```sh
composer install --no-dev --optimize-autoloader
vendor/bin/drush --yes updatedb
vendor/bin/drush --yes config:import
vendor/bin/drush --yes cache:rebuild
```

## Development

```sh
composer install
```

```sh
vendor/bin/drush --yes pm:enable debile_djokes_fixtures
vendor/bin/drush --yes content-fixtures:load
vendor/bin/drush --yes pm:uninstall content_fixtures
```
