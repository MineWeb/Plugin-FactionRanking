<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title"><?= $Lang->get('RANKING_FACTION__PAGE_TITLE') ?></h3>
        </div>
        <div class="box-body">
          <form action="<?= $this->Html->url('/admin/factions/save') ?>" data-ajax="true">

            <div class="form-group">
              <label><?= $Lang->get('RANKING_FACTION__SELECT_CACHE') ?></label>
              <input type="text" class="form-control" name="cache" placeholder="Ex: 2" value="<?= $data['cache'] ?>">
            </div>
            <div class="form-group">
              <label><?= $Lang->get('RANKING_FACTION__SELECT_AFFICH') ?></label>
              <select style="height:170px;" class="form-control" name="affich" multiple>
                <option value="name"<?= (in_array('name', $data['affich'])) ? ' selected' : '' ?>><?= $Lang->get('RANKING_FACTION__AFFICH_NAME') ?></option>
                <option value="description"<?= (in_array('description', $data['affich'])) ? ' selected' : '' ?>><?= $Lang->get('RANKING_FACTION__AFFICH_DESCRIPTION') ?></option>
                <option value="players"<?= (in_array('players', $data['affich'])) ? ' selected' : '' ?>><?= $Lang->get('RANKING_FACTION__AFFICH_PLAYERS') ?></option>
                <option value="leader"<?= (in_array('leader', $data['affich'])) ? ' selected' : '' ?>><?= $Lang->get('RANKING_FACTION__AFFICH_LEADER') ?></option>
                <option value="power"<?= (in_array('power', $data['affich'])) ? ' selected' : '' ?>><?= $Lang->get('RANKING_FACTION__AFFICH_POWER') ?></option>
                <option value="claims"<?= (in_array('claims', $data['affich'])) ? ' selected' : '' ?>><?= $Lang->get('RANKING_FACTION__AFFICH_CLAIMS') ?></option>
                <option value="points"<?= (in_array('points', $data['affich'])) ? ' selected' : '' ?>><?= $Lang->get('RANKING_FACTION__AFFICH_POINTS') ?></option>
              </select>
            </div>

            <div class="form-group">
              <label><?= $Lang->get('RANKING_FACTION__SELECT_CALCUL_POINTS') ?></label>
              <select class="form-control" name="calcul_points" multiple>
                <option value="players"<?= (in_array('players', $data['calcul_points'])) ? ' selected' : '' ?>><?= $Lang->get('RANKING_FACTION__AFFICH_PLAYERS') ?></option>
                <option value="power"<?= (in_array('power', $data['calcul_points'])) ? ' selected' : '' ?>><?= $Lang->get('RANKING_FACTION__AFFICH_POWER') ?></option>
                <option value="claims"<?= (in_array('claims', $data['calcul_points'])) ? ' selected' : '' ?>><?= $Lang->get('RANKING_FACTION__AFFICH_CLAIMS') ?></option>
              </select>
              <small><?= $Lang->get('RANKING_FACTION__SELECT_CALCUL_POINTS_INFOS') ?></small>
            </div>

            <div class="form-group">
              <label><?= $Lang->get('RANKING_FACTION__SELECT_SERVER_ID') ?></label>
              <select class="form-control" name="serverid">
                <?php
                foreach ($servers as $id => $name) {
                  echo '<option value="'.$id.'"';
                  echo ($id == $data['serverid']) ? ' selected' : '';
                  echo '>'.$name.'</option>';
                }
                ?>
              </select>
            </div>

            <div class="pull-right">
              <button class="btn btn-primary" type="submit"><?= $Lang->get('GLOBAL__SUBMIT') ?></button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
