# typo3_composer_migrator
Auto generates a composer.json of a legacy typo3 project

This script adds composer packages of currently installed extensions (core and 3rd party)
with the help of PackageStates.php and typo3conf/ext/* info.

When switching TYPO3 legacy projects to composer mode as describe here:
https://docs.typo3.org/m/typo3/guide-installation/main/en-us/MigrateToComposer/MigrationSteps.html
you need to setup your composer.json

This script does this job for you.


## Usage:

* We assume you already have your legacy project in a sub folder "public".
* Place this file inside your project root (one level above "public").
* Make sure to adjust your project and / or client name with the script.

> php composer_migrator.php

When running this script a composer.json will be generated which is ready for a "composer install"
once your project structure has been adopted accordingly.
