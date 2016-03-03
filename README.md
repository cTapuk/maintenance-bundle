# MaintenanceBundle

## Installation
```
# Bash

composer require vesax/maintenance-bundle dev-master
```

```
# AppKernel.php
if (in_array($this->getEnvironment(), array('dev', 'test'), true)) {
    $bundles[] = new Vesax\MaintenanceBundle\VesaxMaintenanceBundle();
}
```

# Configuration (optional)
```
vesax_maintenance:
    allowed_clients:
        - "127.0.0.1"
```

## Usage Examples
```
php app/console app:disable
php app/console app:disable --start="02:00"
php app/console app:disable --start="2016-03-03 02:00"
php app/console app:disable --start="2016-03-03 02:00" --end="06:00"
php app/console app:disable --add-clients
php app/console app:disable --no-interactive

php app/console app:enable
```
