<?php

/**
 * Generate a composer.json of a legacy TYPO3 project
 * It adds composer packages of currently installed extensions (core and 3rd party)
 * with the help of PackageStates.php and typo3conf/ext/* info
 *
 * Usage:
 * We assume you already have your legacy project in a sub folder "public".
 * Place this file inside your project root (one level above "public").
 * Make sure to adjust your project and / or client name.
 * > php composer_migrator.php
 * When running this script a composer.json will be generated which is ready for a "composer install".
 * once you project structure has been adopted according to:
 * https://docs.typo3.org/m/typo3/guide-installation/main/en-us/MigrateToComposer/MigrationSteps.html
 */

class ComposerMigrator
{
    protected string $coreExtensionComposerPrefix = 'typo3/cms-';
    protected string $targetCoreVersion = '^11.5';

    protected array $composerBase = [
        "name" => "clientname/typo3",
	    "description" => "Client Name TYPO3 CMS",
	    "license" => "GPL-2.0-or-later",
	    "repositories" => [
		    [
                "type" => "path",
                "url" => "./packages/*/"
            ]
        ]
    ];

    protected array $composerConfig = [
        "config" => [
            "allow-plugins"=> [
                "typo3/class-alias-loader" => true,
                "typo3/cms-composer-installers" => true
            ]
        ]
    ];

    protected array $composerScripts = [
        "scripts" => [
            "typo3-cms-scripts" => [
                "typo3cms install:fixfolderstructure"
            ],
            "post-autoload-dump" => [
                "@typo3-cms-scripts"
            ]
        ]
    ];

    public function run(): void
    {
        $extensions = $this->getInstalledExtensions();

        $composerData = array_merge($this->composerBase, $extensions, $this->composerConfig, $this->composerScripts);

        file_put_contents( 'composer.json', json_encode($composerData, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) );
    }

    protected function getInstalledExtensions(): array
    {
        $extension['require'] = [];
        $packageStates = require 'public/typo3conf/PackageStates.php';

        foreach ($packageStates['packages'] as $extKey => $package) {

            if (str_contains($package['packagePath'], 'typo3/sysext') ) {
                $packageName = $this->coreExtensionComposerPrefix . str_replace('_', '-', $extKey);
                $extension['require'][$packageName] = $this->targetCoreVersion;
            } else {
                // get 3rd party extension package name
                $packageInfo = $this->getPackageInfo($extKey);
                $extension['require'][$packageInfo['name']] = $packageInfo['version'];
            }
        }

        return $extension;
    }

    protected function getPackageInfo($extKey)
    {
        $packageInfo = [];
        $extensionPath = 'public/typo3conf/ext/' . $extKey . '/';
        $packageInfoJson = file_get_contents($extensionPath . '/composer.json');
        $packageInfoAll = json_decode($packageInfoJson, true);
        $packageInfo['name'] = $packageInfoAll['name'];
        $packageInfo['version'] = $packageInfoAll['version'] ?? $this->getVersionInfoFromExtensionConfig($extensionPath, $extKey);

        return $packageInfo;
    }

    protected function getVersionInfoFromExtensionConfig($extensionPath, $extKey): string
    {
        $_EXTKEY = $extKey;
        include($extensionPath . '/ext_emconf.php');
        return $EM_CONF[$extKey]['version'];
    }

}

$Migrator = new ComposerMigrator();
$Migrator->run();
unset($Migrator);
