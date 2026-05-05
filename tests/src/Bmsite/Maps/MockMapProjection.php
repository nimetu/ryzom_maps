<?php

namespace Bmsite\Maps;

class MockMapProjection extends MapProjection
{
    private $mockWorldZones = array(
        'world' => array(array(0, 14160), array(14160, 0)),
        'fyros' => array(array(2920, 3836), array(7400, 636)),
        'matis' => array(array(7760, 7872), array(13680, 352)),
        'grid' => array(array(-108000, 47520), array(0, 0)),
    );

    private $mockServerZones = array(
        'fyros' => array(array(15840, -27040), array(20320, -23840)),
        'matis' => array(array(320, -7840), array(6240, -320)),
        'place_pyr' => array(array(18400, -24720), array(19040, -24240)),
        'place_yrkanis' => array(array(4640, -3680), array(4800, -3200)),
        'grid' => array(array(0, -47520), array(108000, 0)),
    );

    public function __construct()
    {
        $this->setWorldZones($this->mockWorldZones);
        $this->setServerZones($this->mockServerZones);
    }
}
