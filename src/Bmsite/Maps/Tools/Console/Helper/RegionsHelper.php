<?php
/**
 * Ryzom Maps
 *
 * @author Meelis Mägi <nimetu@gmail.com>
 * @copyright (c) 2014 Meelis Mägi
 * @license http://opensource.org/licenses/LGPL-3.0
 */

namespace Bmsite\Maps\Tools\Console\Helper;


use Symfony\Component\Console\Helper\Helper;

class RegionsHelper extends Helper
{

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     *
     * @api
     */
    public function getName()
    {
        return 'regions';
    }

    /**
     * @param string $sheetid
     *
     * @return int
     */
    public function getRegionForce($sheetid)
    {
        /*
          region_fyreruins
          region_hopeforest
          region_northspire

          region_mistvalley
          region_karavanexclusionzone

          region_newbieland
          region_newbieland_blight_zone
          region_newbieland_hunting_grounds
          region_newbieland_kitins_jungle
          region_newbieland_shining_lake
          region_newbieland_starting_zone
          region_newbieland_the_shattered_ruins
         */

        switch ($sheetid) {
            //case 'fyros' : return 'FFFFFF';
            //case 'matis' : return 'FFFFFF';
            //case 'tryker': return 'FFFFFF';
            //case 'zorai' : return 'FFFFFF';
            case 'region_libertylake':
            case 'region_citiesofintuition':
            case 'region_majesticgarden':
            case 'region_imperialdunes':
                return 50; //'6666FF'; //'6E6EE1';
            case 'region_dewdrops':
            case 'region_windsofmuse':
            case 'region_oflovaksoasis':
            case 'region_maidengrove':
            case 'region_fleetinggarden':
                return 100; //'FFFF66'; //'FFFFAC';
            case 'region_restingwater':
            case 'region_thefount':
            case 'region_sawdustmines':
            case 'region_frahartowers':
            case 'region_havenofpurity':
            case 'region_knollofdissent':
            case 'region_the_windy_gate':
            case 'region_gate_of_obscurity':
                return 150; //'FF9933'; //'FFB464';
            case 'region_bountybeaches':
            case 'region_enchantedisle':
            case 'region_outlawcanyon':
            case 'region_dunesofexil':
            case 'region_upperbog':
            case 'region_thesavagedunes':
            case 'region_hereticshovel':
            case 'region_hiddensource':
            case 'region_knotofdementia':
            case 'region_groveofumbra':
            case 'region_the_abyss_of_ichor':
            case 'region_the_elusive_forest':
            case 'region_the_trench_of_trials':
            case 'region_nexus':
                return 200; //'C83232'; //'FF6666'; //'C83232';
            case 'region_lagoonsofloria':
            case 'region_thescorchedcorridor':
            case 'region_thevoid':
            case 'region_groveofconfusion':
            case 'region_the_land_of_continuty':
            case 'region_the_sunken_city':
            case 'region_forbidden_depths':
            case 'region_the_under_spring':
                return 250; // '963296'; //'FF66FF'; //'963296';
            default:
                return 0; //'FFFFFF';
        }
        //switch
    }
}
