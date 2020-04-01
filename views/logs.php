<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
function wp_tracker_logs_details()
{
    ?>
    <link rel="stylesheet" href="<?php echo AddFile::addFiles('assets/css', 'bootstrap.min', 'css', true); ?>">
    <div class="container" style="margin-top: 5%">
        <div class="col-md-12">
            <h3>Api call logs</h3>
            <table class="table table-bordered table-striped">
                <colgroup>
                    <col class="col-xs-1">
                    <col class="col-xs-7">
                </colgroup>
                <thead>
                <tr>
                    <th>Time</th>
                    <th>Description</th>
                    <th>Api</th>
                    <th>Order Id</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <th scope="row"> <code>.active</code> </th>
                    <td>Applies the hover color to a particular row or cell</td>
                    <th scope="row"> <code>.active</code> </th>
                    <th scope="row"> <code>.active</code> </th>
                    <th scope="row"> <div class="label label-danger">.active</div> </th>
                    <th scope="row"> <div class="label label-danger">.active</div> </th>
                </tr>
                </tbody>
            </table>
            <button class="btn btn-warning">Load More ... </button>
        </div>
    </div>
    <?php
}