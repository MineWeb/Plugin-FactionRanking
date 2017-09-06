<div class="container">
    <div class="row">

        <div class="col-md-6">
            <h1 style="display: inline-block;"><?= $Lang->get('RANKING_FACTION__PAGE_TITLE') ?></h1>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <?php if($cache_time) { ?>
                        <div class="alert alert-info"><?= str_replace('{CACHE_TIME}', $cache_time, $Lang->get('RANKING_FACTION__CACHED')) ?></div>
                    <?php } ?>

                    <div id="data">
                      <table class="table table-bordered dataTable">
                          <thead>
                              <tr>
                                  <th>#</th>
                                  <?php foreach ($affich as $key => $value) {
                                      echo '<th>'.$Lang->get('RANKING_FACTION__AFFICH_'.strtoupper($value)).'</th>';
                                  } ?>
                              </tr>
                          </thead>
                          <tbody>
                          </tbody>
                      </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->Html->css('dataTables.bootstrap.css'); ?>
<?= $this->Html->script('jquery.dataTables.min.js') ?>
<?= $this->Html->script('dataTables.bootstrap.min.js') ?>
<script>
    $(function () {
      $('table.dataTable').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": false,
        "ordering": false,
        "info": false,
        "autoWidth": false,
        'searching': true,
        "language": {
            "infoEmpty": "<?= $Lang->get('RANKING_FACTION__EMPTY_DATA') ?>",
            "loadingRecords": "<?= $Lang->get('LOADING') ?>...",
            "search":         "<?= $Lang->get('RANKING_FACTION__SEARCH') ?>:",
            "zeroRecords":    "<?= $Lang->get('RANKING_FACTION__ZERO_RECORDS') ?>",
            "paginate": {
                "first":      "<?= $Lang->get('RANKING_FACTION__FIRST') ?>",
                "last":       "<?= $Lang->get('RANKING_FACTION__LAST') ?>",
                "next":       "<?= $Lang->get('RANKING_FACTION__NEXT') ?>",
                "previous":   "<?= $Lang->get('RANKING_FACTION__PREVIOUS') ?>"
            },
        },
        'ajax': '#',
        "fnServerData": function ( sSource, aoData, fnCallback, oSettings ) {
          oSettings.jqXHR = $.ajax({
            "dataType": 'json',
            "type": "POST",
            "url": sSource,
            "data": aoData,
            "success": function(data) {
              if(data.length == 0) {
                $('div#data').fadeOut(100, function() {
                  $(this).html('<div class="alert alert-danger"><?= $Lang->get('RANKING_FACTION__FACTIONS_NOT_FOUND') ?></div>').fadeIn(100);
                });
              } else {
                fnCallback(data);
              }
            },
            "error": function(data) {
              $('table#data').fadeOut(100, function() {
                $(this).html('<div class="alert alert-danger"><?= $Lang->get('ERROR__INTERNAL_ERROR') ?></div>').fadeIn(100);
              });
            }
          });
        },
        'columns': [
            { "data" : "position"},
            <?php foreach ($affich as $key => $value) {
                if($value == "leader") {
                    echo '{ data : "'.$value.'[, ]"},';
                } else {
                    echo '{ data : "'.$value.'"},';
                }
            } ?>
        ],
        "fnInitComplete": function(oSettings, json) {
          $("[data-toggle=popover]").each(function(e) {
            $(this).popover();
          });
        }
      });
    });
</script>
