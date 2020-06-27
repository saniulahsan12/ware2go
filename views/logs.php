<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
function bpax_wp_tracker_logs_details() {   ?>
	<link rel="stylesheet" href="<?php echo BpaxAddFile::addFiles( 'assets/css', 'bootstrap.min', 'css', true ); ?>">
	<style>
		.update-nag, .updated, .error, .is-dismissible { display: none !important; }
		@-webkit-keyframes rotating /* Safari and Chrome */ {
			from {
				-webkit-transform: rotate(0deg);
				-o-transform: rotate(0deg);
				transform: rotate(0deg);
			}
			to {
				-webkit-transform: rotate(360deg);
				-o-transform: rotate(360deg);
				transform: rotate(360deg);
			}
		}
		@keyframes rotating {
			from {
				-ms-transform: rotate(0deg);
				-moz-transform: rotate(0deg);
				-webkit-transform: rotate(0deg);
				-o-transform: rotate(0deg);
				transform: rotate(0deg);
			}
			to {
				-ms-transform: rotate(360deg);
				-moz-transform: rotate(360deg);
				-webkit-transform: rotate(360deg);
				-o-transform: rotate(360deg);
				transform: rotate(360deg);
			}
		}
		.loader-icon {
			-webkit-animation: rotating 2s linear infinite;
			-moz-animation: rotating 2s linear infinite;
			-ms-animation: rotating 2s linear infinite;
			-o-animation: rotating 2s linear infinite;
			animation: rotating 2s linear infinite;
		}
		pre {outline: 1px solid #ccc; padding: 5px; margin: 5px; }
		.string { color: green; }
		.number { color: darkorange; }
		.boolean { color: blue; }
		.null { color: magenta; }
		.key { color: red; }

	</style>
	<div class="col-md-12 table-responsive" style="margin-top: 5%">
		<h3>Ware2Go Api logs</h3>
		<br>
		<table class="table table-bordered table-striped">
			<colgroup>
				<col class="col-xs-1">
				<col class="col-xs-7">
			</colgroup>
			<thead>
			<tr>
				<th>ID</th>
				<th>Time</th>
				<th>Api</th>
				<th>Method</th>
				<th>Order ID</th>
				<th>Status</th>
				<th>Action</th>
			</tr>
			</thead>
			<tbody id="ware2go-table-row">

			</tbody>
		</table>
	</div>
	<br>
	<div class="col-md-12">
		<button class="btn btn-warning bpax-load-api-logs">Load More</button>
	</div>
	<!-- Modal -->
	<div class="modal fade" id="apiDetails" tabindex="-1" role="dialog" aria-labelledby="apiDetailsLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="apiDetailsLabel">View Details</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<h3>Posted Data</h3>
					<pre id="posted-data"></pre>
					<br>
					<h3>Response Data</h3>
					<pre id="response-data"></pre>
				</div>
			</div>
		</div>
	</div>
	<script src="<?php echo BpaxAddFile::addFiles( 'assets/js', 'bootstrap.min', 'js', true ); ?>"></script>
	<script>
		var bapx__nonce = "<?php echo wp_create_nonce( 'bpax_submit' ); ?>";
		var bpax__api_page = 1;
		var bpax__data_per_page = 20;
		var loadMoreBtn = jQuery(".bpax-load-api-logs");
		loadMoreBtn.click( function() {

			jQuery.ajax({
				type : "get",
				url : "<?php echo admin_url( 'admin-ajax.php' ); ?>",
				responseType: 'application/json',
				data : {
					action: "bpax_load_api_logs",
					page : bpax__api_page,
					data_per_page : bpax__data_per_page,
					nonce: bapx__nonce
				},
				beforeSend: function() {
					loadMoreBtn.attr('disabled', true);
					loadMoreBtn.html('<span class="dashicons dashicons-image-rotate loader-icon"></span> Loading ...');
				},
				success: function(response) {
					if(response.length === 0){
						loadMoreBtn.remove();
					}
					bpax__api_page = bpax__api_page + 1;
					generateTable(response);
					loadMoreBtn.attr('disabled', false);
					loadMoreBtn.html('Load More');
				}
			})
		});

		jQuery(document).ready(function(){
			loadMoreBtn.trigger('click');
		});

		function generateTable(rows) {
			if(rows.length === 0) return;
			for(var key in rows){
				jQuery('#ware2go-table-row').append(
					jQuery(
						'<tr>' +
						'                    <th scope="row">' + rows[key].id + '</th>' +
						'                    <th scope="row">' + rows[key].time + '</th>' +
						'                    <th scope="row">' + rows[key].api + '</th>' +
						'                    <th scope="row">' + rows[key].method + '</th>' +
						'                    <th scope="row">' + rows[key].order_id + '</th>' +
						'                    <th scope="row"> <div class="label label-info">' + rows[key].status + '<div class="label label-danger"> </th>' +
						'                    <th scope="row"> <textarea class="hidden" id="post-data-' + rows[key].id + '">' + rows[key].data + '</textarea><textarea class="hidden" id="response-data-' + rows[key].id + '">' + rows[key].response + '</textarea><button class="btn btn-success" onclick="bpaxLoadAPIDetails(' + rows[key].id + ')">View Details</button> </th>' +
						'                </tr>'
					)
				);
			}
		}

		function bpaxLoadAPIDetails(rowId){
			var posted_data = jQuery('#post-data-' + rowId).val();
			var response_data = jQuery('#response-data-' + rowId).val() || '{}';

			posted_data = JSON.stringify(JSON.parse(posted_data), undefined, 4);
			response_data = JSON.stringify(JSON.parse(response_data), undefined, 4);

			jQuery('#response-data').html(syntaxHighlight(response_data));
			jQuery('#posted-data').html(syntaxHighlight(posted_data));
			jQuery('#apiDetails').modal('show');
		}

		function syntaxHighlight(json) {
			json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
			return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
				var cls = 'number';
				if (/^"/.test(match)) {
					if (/:$/.test(match)) {
						cls = 'key';
					} else {
						cls = 'string';
					}
				} else if (/true|false/.test(match)) {
					cls = 'boolean';
				} else if (/null/.test(match)) {
					cls = 'null';
				}
				return '<span class="' + cls + '">' + match + '</span>';
			});
		}

	</script>
	<?php
}
