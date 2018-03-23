<?php
/**
 * Created by JetBrains PhpStorm.
 * User: meelis
 * Date: 7/24/13
 * Time: 10:44 AM
 * To change this template use File | Settings | File Templates.
 */

use Bmsite\Maps\ResourceLoader;
use Bmsite\Maps\Tools\Console\Helper\ResourceHelper;

require_once dirname(__DIR__).'/vendor/autoload.php';

$appRoot = dirname(__DIR__);

$loader = new ResourceLoader();
$serverZonesFile = $loader->getFilePath('server.json');
$worldZonesFile = $loader->getFilePath('world.json');
$labelsFile = $loader->getFilePath('labels.json');
$areasFile = $loader->getFilePath('areas.json');

$resources = new ResourceHelper();
$resources->set('app.path', $appRoot);
$resources->set('server.json.file', $serverZonesFile);
$resources->set('world.json.file', $worldZonesFile);
$resources->set('labels.json.file', $labelsFile);
$resources->set('areas.json.file', $areasFile);

$helperSet = new \Symfony\Component\Console\Helper\HelperSet();
$helperSet->set($resources);
$helperSet->set(new \Bmsite\Maps\Tools\Console\Helper\TranslateHelper());
// TODO: region force from json file
$helperSet->set(new \Bmsite\Maps\Tools\Console\Helper\RegionsHelper());

$cli = new \Symfony\Component\Console\Application('Bmsite Ryzom Maps', '0.1');
$cli->setCatchExceptions(true);
$cli->setHelperSet($helperSet);
$cli->addCommands(
    array(
        new \Bmsite\Maps\Tools\Console\Command\BuildJsonFiles(),
    )
);
$cli->run();

