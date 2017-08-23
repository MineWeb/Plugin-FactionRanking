<?php
/*
Available methods

getPlayerFaction
getAllFactions
getFactionPlayers
getFactionPowers
getFactionMaxPowers
getFactionClaims
getFactionDescription
getFactionLeader
getFactionOfficers

*/

class RankingController extends AppController{

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Security->unlockedActions = array('index');
	}

	function index() {
		$this->set('title_for_layout', $this->Lang->get('RANKING_FACTION__PAGE_TITLE'));

		// On affiche une dataTable vide
		// Avec comme colonne possible (configuration admin) : "Position", "Nom", "Description", "Chef", "Nombre de membres", "Nombre de claims", "Power"

		// Config
		$this->loadModel('FactionRanking.RfConfiguration');
		$config = $this->RfConfiguration->getConfig();
		$affich = $config['affich'];
		$calcul_points = $config['calcul_points'];

		$factions_ignored = Configure::read('RankingFaction.config.ignore_factions');
		if($config['cache'] > 0) {
			$cache = $config['cache']; // hours ou false
		} else {
			$cache = false;
		}

		if($cache) {
			$cache_folder = ROOT.DS.APP_DIR.DS.'tmp'.DS.'cache'.DS.'plugins'.DS;
			$cache_filename = 'ranking_faction.cache';
			if(!is_dir($cache_folder)) {
				mkdir($cache_folder, 0755, true);
			}
		}

		$this->set(compact('affich'));
		$this->set('cache_time', $cache);


		if($this->request->is('ajax')) {
			$this->autoRender = false;
			// Appelée par la dataTable après le chargement de la page, on lui renvoie du JS avec les données qu'on a.
			// Get factions
			if(!$cache || $cache && !file_exists($cache_folder.$cache_filename) || strtotime('+ '.$cache.' hours', filemtime($cache_folder.$cache_filename)) < time()) {
				$factions = $this->Server->call(array('getAllFactions' => 'server'), true, $config['serverid']);

				if(isset($factions['getAllFactions']) && $factions['getAllFactions'] != "none" && $factions['getAllFactions'] != "PLUGIN_NOT_FOUND") {

					App::import('FactionRanking.Vendor', 'MinecraftColors');

					$factions = explode(', ', $factions['getAllFactions']);
					foreach ($factions as $key => $factionName) {

						if(!in_array($factionName, $factions_ignored)) { // si la faction ne doit pas être ignorée

							// On fais la requête
							$serverData = $this->Server->call(array(
								'getFactionPlayers' => $factionName,
								'getFactionOfficers' => $factionName,
								'getFactionLeader' => $factionName,
								'getFactionPowers' => $factionName,
								'getFactionDescription' => $factionName,
	 							'getFactionClaims' => $factionName
							), true, $config['serverid']);

							// les infos
							$data[$factionName]['name'] = $factionName;

							if(isset($serverData['getFactionPlayers']) && $serverData['getFactionPlayers'] != 'none') {
								$players = $serverData['getFactionPlayers'];
								$data[$factionName]['players'] = count(explode(', ', $players));
								$data[$factionName]['players_pseudo'] = explode(', ', $players);
							} else {
								$data[$factionName]['players'] = 0;
								$data[$factionName]['players_pseudo'] = array();
							}

							if(isset($serverData['getFactionOfficers']) && $serverData['getFactionOfficers'] != 'none') {
								$officers = $serverData['getFactionOfficers'];
								$data[$factionName]['officers'] = explode(', ', $officers);
							} else {
								$data[$factionName]['officers'] = array();
							}

							if(isset($serverData['getFactionLeader']) && $serverData['getFactionLeader'] != 'none') {
								$leader = $serverData['getFactionLeader'];
								$data[$factionName]['leader'] = explode(', ', $leader);
							} else {
								$data[$factionName]['leader'] = array();
							}

							$data[$factionName]['power'] = (isset($serverData['getFactionPowers'])) ? $serverData['getFactionPowers'] : 0;

							$data[$factionName]['description'] = (isset($serverData['getFactionDescription'])) ? $serverData['getFactionDescription'] : '';
							// Parsage de couleurs
							$data[$factionName]['description'] = MinecraftColors::convertToHTML($data[$factionName]['description']);

							$data[$factionName]['claims'] = (isset($serverData['getFactionClaims'])) ? $serverData['getFactionClaims'] : 0;

							// calcul des points
							$data[$factionName]['points'] = 0;

							if(is_array($calcul_points)) { // si on doit additioner plusieurs données

								foreach ($calcul_points as $k => $v) {
									$data[$factionName]['points'] = $data[$factionName]['points'] + intval($data[$factionName][$v]);
								}

							} else { // sinon c'est avec une seule donnée
								$data[$factionName]['points'] = $data[$factionName][$calcul_points];
							}

						}
					}
					// on classe les factions
					usort($data, function($a, $b){
						return $a['points'] < $b['points'];
					});

					$i = 0;
					foreach ($data as $key => $value) {
						$i++;
						$data['data'][$key] = $value;
						if(in_array('players', $affich)) {
							if($data[$key]['players'] > 0) {
								$data['data'][$key]['players'] = $data[$key]['players'].' &nbsp;&nbsp;<button type="button" class="btn btn-info btn-xs" data-container="body" data-toggle="popover" data-placement="top" data-content="'.implode(', ', $data[$key]['players_pseudo']).'">'.$this->Lang->get('RANKING_FACTION__VIEW').'</button>';
							} else {
								$data['data'][$key]['players'] = $this->Lang->get('RANKING_FACTION__NO_PLAYER');
							}
						} else {
							unset($data['data'][$key]['players']);
							unset($data['data'][$key]['players_pseudo']);
						}
						if(!in_array('officers', $affich)) {
							unset($data['data'][$key]['officers']);
						}
						if(!in_array('leader', $affich)) {
							unset($data['data'][$key]['leader']);
						}
						if(!in_array('power', $affich)) {
							unset($data['data'][$key]['power']);
						}
						if(!in_array('description', $affich)) {
							unset($data['data'][$key]['description']);
						}
						if(!in_array('claims', $affich)) {
							unset($data['data'][$key]['claims']);
						}
						$data['data'][$key]['position'] = $i;
						unset($data[$key]);
					}

					if($cache) {
						file_put_contents($cache_folder.$cache_filename, json_encode($data));
					}

				} else {
					$data = array();
					$this->log('FactionRankingPlugin : Factions not found');
				}

			} else {
				$data = json_decode(file_get_contents($cache_folder.$cache_filename), true);
			}

			echo json_encode($data);
		}

	}

	function admin_index() {
		$this->layout = 'admin';
		$this->set('title_for_layout', $this->Lang->get('RANKING_FACTION__PAGE_TITLE'));

		if($this->isConnected && $this->User->isAdmin()) {

			$this->loadModel('FactionRanking.RfConfiguration');
			$config = $this->RfConfiguration->getConfig();

			$this->loadModel('Server');
			$servers = $this->Server->findSelectableServers();
			$this->set(compact('servers'));

			$this->set('data', $config);
	    } else {
	    	$this->redirect('/');
	    }
	}

	function admin_save() { // appelée en ajax pour sauvegardé les données
		$this->autoRender = false;
		if($this->isConnected && $this->User->isAdmin()) {
			if($this->request->is('ajax')) {
				if(!empty($this->request->data['cache']) || $this->request->data['cache'] == 0 && !empty($this->request->data['affich']) && !empty($this->request->data['serverid']) && !empty($this->request->data['calcul_points'])) {
					$this->loadModel('FactionRanking.RfConfiguration');

					$this->RfConfiguration->read(null, 1);
					$this->RfConfiguration->set(array(
						'cache' => $this->request->data['cache'],
						'affich' => serialize($this->request->data['affich']),
						'calcul_points' => serialize($this->request->data['calcul_points']),
						'serverid' => $this->request->data['serverid']
					));
					$this->RfConfiguration->save();

					$this->History->set('EDIT_RANKING_FACTION_CONFIG', 'faction');
					echo json_encode(array('statut' => true, 'msg' => $this->Lang->get('RANKING_FACTION__SUCCESS_EDIT')));
				} else {
					echo json_encode(array('statut' => false, 'msg' => $this->Lang->get('COMPLETE_ALL_FIELDS')));
				}
			} else {
				throw new InternalErrorException();
			}
		} else {
			throw new InternalErrorException();
		}
	}

}