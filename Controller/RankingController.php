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


		if (!$this->request->is('ajax'))
		    return;
        $this->autoRender = false;
        $this->response->type('json');
        if ($cache && file_exists($cache_folder.$cache_filename) && strtotime('+ '.$cache.' hours', filemtime($cache_folder.$cache_filename)) > time())
            return $this->response->body(file_get_contents($cache_folder.$cache_filename));

        $factions = $this->Server->call('GET_FACTIONS', $config['serverid']);
        if (!$factions || !isset($factions['GET_FACTIONS']) || empty($factions['GET_FACTIONS']))
            return $this->response->body(json_encode([]));

        App::import('FactionRanking.Vendor', 'MinecraftColors');
        $data = [];
        foreach ($factions['GET_FACTIONS'] as $faction) {
            if (in_array($faction['name'], $factions_ignored))
                continue;
            $data[$faction['name']] = [
                'name' => $faction['name'],
                'players' => count($faction['players']),
                'players_pseudo' => $faction['players'],
                'leader' => [$faction['leader']],
                'power' => $faction['power']['current'],
                'description' => MinecraftColors::convertToHTML($faction['description']),
                'claims' => $faction['claims_count'],
                'points' => 0
            ];

            if (is_array($calcul_points))
                foreach ($calcul_points as $value)
                    $data[$faction['name']]['points'] = $data[$faction['name']]['points'] + intval($data[$faction['name']][$value]);
            else
                $data[$faction['name']]['points'] = $data[$faction['name']][$calcul_points];

        }

        // on classe les factions
        usort($data, function($a, $b){
            return $a['points'] < $b['points'];
        });

        $pos = 0;
        foreach ($data as $key => $value) {
            $pos++;
            $data['data'][$key] = $value;
            if (in_array('players', $affich)) {
                if ($data[$key]['players'] > 0)
                    $data['data'][$key]['players'] = $data[$key]['players'].' &nbsp;&nbsp;<button type="button" class="btn btn-info btn-xs" data-container="body" data-toggle="popover" data-placement="top" data-content="'.implode(', ', $data[$key]['players_pseudo']).'">'.$this->Lang->get('RANKING_FACTION__VIEW').'</button>';
                else
                    $data['data'][$key]['players'] = $this->Lang->get('RANKING_FACTION__NO_PLAYER');
            } else {
                unset($data['data'][$key]['players']);
                unset($data['data'][$key]['players_pseudo']);
            }
            if (!in_array('leader', $affich))
                unset($data['data'][$key]['leader']);
            if (!in_array('power', $affich))
                unset($data['data'][$key]['power']);
            if (!in_array('description', $affich))
                unset($data['data'][$key]['description']);
            if (!in_array('claims', $affich))
                unset($data['data'][$key]['claims']);
            $data['data'][$key]['position'] = $pos;
            unset($data[$key]);
        }

        if ($cache)
            file_put_contents($cache_folder.$cache_filename, json_encode($data));
        return $this->response->body(json_encode($data));
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
