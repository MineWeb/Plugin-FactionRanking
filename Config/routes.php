<?php
Router::connect('/rankingfactions/*', array('controller' => 'Ranking', 'action' => 'index', 'plugin' => 'FactionRanking'));
Router::connect('/factions/*', array('controller' => 'Ranking', 'action' => 'index', 'plugin' => 'FactionRanking'));
Router::connect('/admin/factions', array('controller' => 'Ranking', 'action' => 'index', 'plugin' => 'FactionRanking', 'admin' => true));
Router::connect('/admin/factions/', array('controller' => 'Ranking', 'action' => 'index', 'plugin' => 'FactionRanking', 'admin' => true));
Router::connect('/admin/factions/:action', array('controller' => 'Ranking', 'action' => ':action', 'plugin' => 'FactionRanking', 'admin' => true));
