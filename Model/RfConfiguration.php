<?php
class RfConfiguration extends FactionRankingAppModel {

  public $defaultConfig = array(
    'cache' => 0,
    'affich' => array('name', 'description', 'players', 'leader', 'power', 'claims', 'points'),
    'calcul_points' => array('players', 'power', 'claims'),
    'serverid' => 1
  );

  public function getConfig() {
    $return = $this->defaultConfig;

    $config = $this->find('first');
    if(!empty($config)) {
      $return['cache'] = $config['RfConfiguration']['cache'];
      $return['serverid'] = $config['RfConfiguration']['serverid'];
		  $return['affich'] = unserialize($config['RfConfiguration']['affich']);
		  $return['calculpoints'] = unserialize($config['RfConfiguration']['calcul_points']);
    }

    return $return;

  }

}
