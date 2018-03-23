<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps\Tools\Console\Command;

use Bmsite\Maps\Tools\Console\Helper\RegionsHelper;
use Bmsite\Maps\Tools\Console\Helper\ResourceHelper;
use Bmsite\Maps\Tools\Console\Helper\TranslateHelper;
use Ryzom\Sheets\Client\CContinent;
use Ryzom\Sheets\Client\CContLandMark;
use Ryzom\Sheets\Client\WorldSheet;
use Ryzom\Sheets\PackedSheetsLoader;
use Ryzom\Translation\StringsManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BuildJsonFiles
 */
class BuildJsonFiles extends Command
{

    protected function configure()
    {
        $this
            ->setName('bmmaps:json')
            ->setDescription('Build json files')
            ->addOption(
                'ryzom',
                null,
                InputOption::VALUE_REQUIRED,
                'Ryzom data path',
                ''
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ResourceHelper $helper */
        $helper = $this->getHelper('resource');

        $ryzomDataPath = $input->getOption('ryzom');
        if (empty($ryzomDataPath) ||
            !file_exists($ryzomDataPath.'/gamedev.bnp') ||
            !file_exists($ryzomDataPath.'/lmconts.packed') ||
            !file_exists($ryzomDataPath.'/world.packed_sheets')
        ) {
            throw new \InvalidArgumentException("Invalid Ryzom data path. gamedev.bnp, lmconts.packed or world.packed_sheets not found");
        }

        $psLoader = new PackedSheetsLoader($ryzomDataPath);

        // labels.json
        $output->write('<info>building labels.json</info>...');

        /** @var TranslateHelper $translator */
        $translator = $this->getHelper('translator');

        /** @var StringsManager $sm */
        $sm = $translator->load($ryzomDataPath);

        $lmconts = $psLoader->load('lmconts');
        $continents = $lmconts->get('continents');
        $this->buildLabels($continents, $sm, $helper->get('labels.json.file'));
        $output->writeln('');

        // region polygons from lmconts
        $output->write('<info>building areas.json</info>...');
        $this->buildRegionPolys($continents, $sm, $helper->get('areas.json.file'));
        $output->writeln('');

        // server.json
        $output->write('<info>building server.json</info>...');

        $ps = $psLoader->load('world');
        // 6 == sheetid for 'ryzom.world'
        $world = $ps->get(6);
        if (!$world) {
            throw new \RuntimeException("Failed to load world.packed_sheets");
        }
        $this->buildServerZones($world, $helper->get('server.json.file'));
        $output->writeln('');
    }

    /**
     * @param WorldSheet $world
     * @param string $outFile
     */
    protected function buildServerZones(WorldSheet $world, $outFile)
    {
        $include = array(
            'continent_fyros',
            'continent_matis',
            'continent_tryker',
            'continent_zorai',
            'continent_bagne',
            'continent_route_gouffre',
            'continent_sources',
            'continent_terre',
            'continent_nexus',
            'cont_newbieland',
            'cont_kitiniere',
            'place_matis_island_1',
            'place_matis_island_2',
            'place_pyr',
            'place_dyron',
            'place_thesos',
            'place_yrkanis',
            'place_natae',
            'place_davae',
            'place_avalae',
            'place_avendale',
            'place_crystabell',
            'place_fairhaven',
            'place_windermeer',
            'place_zora',
            'place_hoi_cho',
            'place_jen_lai',
            'place_min_cho',
        );

        $json = array(
            'grid' => array(array(0, -47520), array(108000, 0))
        );
        foreach ($world->Maps as $map) {
            $key = strtolower($map->Name);

            if (in_array($key, $include)) {
                //echo " + ({$key}, {$map->ContinentName})\n";
                $json[$key] = array(
                    array((int)$map->MinX, (int)$map->MinY),
                    array((int)$map->MaxX, (int)$map->MaxY),
                );
            } else {
                //echo " - ({$key}, {$map->ContinentName})\n";
            }
        }
        file_put_contents($outFile, json_encode($json, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT));
    }

    /**
     * @param CContinent[] $continents
     * @param StringsManager $sm
     * @param string $outFile
     */
    protected function buildLabels(array $continents, StringsManager $sm, $outFile)
    {
        $strings = $sm->getStrings('place');

        $json = array();
        foreach ($continents as $cont) {
            $labels = array();

            $result = $this->filterContLabel($cont, $strings);
            if ($result) {
                list($key, $label) = $result;
                $labels[$key] = $label;
            }

            foreach ($cont->ContLandMarks as $lm) {
                $result = $this->filterLabel($lm, $strings);
                if ($result) {
                    list($key, $label) = $result;

                    if (in_array($key, array('region_matis_island_1', 'region_matis_island_2'))) {
                        // there is no 'continent' text for Almati and Dantes, so make one up
                        // also move region text up a bit or it conflicts other text
                        $label['pos'][1] += 100;
                        $contlabel = $label;
                        $contlabel['lmtype'] = -1;
                        $contlabel['regionforce'] = 0;
                        $labels[$key.'_cont'] = $contlabel;
                    }

                    $labels[$key] = $label;
                }
            }
            $json[$cont->Name] = $labels;
        }

        file_put_contents($outFile, json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    /**
     * Export polygon data from lmconts
     *
     * @param CContinent[] $continents
     * @param StringsManager $sm
     * @param string $outFile
     */
    protected function buildRegionPolys(array $continents, StringsManager $sm, $outFile)
    {
        // index = TContLMType enum
        static $order = array(
            'continent' => 0,
            4 => 1, // region
            0 => 2, // capital
            1 => 3, // village
            3 => 4, // stable
            5 => 5, // place
            6 => 6, // street
            2 => 7, // outpost
            'unknown' => 8,
        );

        $json = array();
        foreach ($continents as $cont) {
            // continent
            $json[$cont->Name] = array(
                'order' => $order['continent'],
                'points' => $this->exportVPoints($cont->Zone->VPoints),
                'areas' => array(),
            );

            // sub regions and areas
            foreach ($cont->ContLandMarks as $lm) {
                $json[$cont->Name]['areas'][$lm->TitleText] = array(
                    'order' => isset($order[$lm->Type]) ? $order[$lm->Type] : $order['unknown'],
                    'points' => $this->exportVPoints($lm->Zone->VPoints)
                );
            }
        }

        file_put_contents($outFile, json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    /**
     * Return flat array from CPrimVector objects
     *
     * @param CPrimVector[] $points
     *
     * @return array [X, Y, ... Xn, Yn]
     */
    private function exportVPoints(array $points)
    {
        $x = false;
        $y = false;
        $ret = array();
        foreach($points as $point) {
            // ingame X is always positive -> next highest
            // ingame Y is always negative -> next lowest
            $ret[] = ceil($point->X*100) / 100;
            $ret[] = floor($point->Y*100) / 100;
        }
        return $ret;
    }

    /**
     * @param CContinent $cont
     * @param array $strings
     *
     * @return array
     */
    private function filterContLabel($cont, array $strings)
    {
        $langs = array_keys($strings);

        // Ryzom only shows continent names in world map, but those are no use here
        // Modify continent info to be used as label
        $key = strtolower($cont->Name);

        // matis_island == 'Old Lands' - do not show
        if ($key == 'matis_island') {
            return false;
        } elseif ($key != 'newbieland') {
            $key = 'continent_'.$key;
        }

        $textArray = array();
        foreach ($langs as $lang) {
            if (isset($strings[$lang][$key])) {
                $textArray[$lang] = $strings[$lang][$key]['name'];
            } else {
                $textArray[$lang] = $key;
            }
        }
        $label = array(
            'pos' => array((int)$cont->ZoneCenter->X, (int)$cont->ZoneCenter->Y),
            'regionforce' => 0,
            'lmtype' => -1,
            'text' => $textArray,
        );
        return array($key, $label);
    }

    /**
     * @param CContLandMark $lm
     * @param array $strings
     *
     * @return array
     */
    private function filterLabel(CContLandMark $lm, array $strings)
    {
        /** @var RegionsHelper $regions */
        $regions = $this->getHelper('regions');

        $langs = array_keys($strings);

        $key = strtolower($lm->TitleText);

        $textArray = array();
        foreach ($langs as $lang) {
            if (isset($strings[$lang][$key])) {
                $textArray[$lang] = $strings[$lang][$key]['name'];
            } else {
                $textArray[$lang] = $key;
            }
        }

        $force = $regions->getRegionForce($lm->TitleText);

        $label = array(
            'pos' => array((int)$lm->Pos->X, (int)$lm->Pos->Y),
            'regionforce' => $force,
            'lmtype' => $lm->Type,
            'text' => $textArray,
        );

        return array($key, $label);
    }
}
