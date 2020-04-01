<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
function woo_ware2go_settings_details()
{

    $status = null;

    if (isset($_POST['save-credence']) && !empty($_POST['acc_api_url']) && !empty($_POST['acc_merchant_id']) && !empty($_POST['acc_token'])) {
        if (
            update_option('acc_api_url', $_POST['acc_api_url']) ||
            update_option('acc_merchant_id', $_POST['acc_merchant_id']) ||
            update_option('acc_token', $_POST['acc_token'])
        ) {
            $status = true;
        }
    }
    ?>
    <link rel="stylesheet" href="<?php echo AddFile::addFiles('assets/css', 'bootstrap.min', 'css', true); ?>">
    <div class="container d-flex justify-content-center" style="margin-top: 5%">
        <div class="col-md-6">
            <div class="jumbotron" style="background: burlywood">
                <img class="img-responsive"
                     style="margin: 0 auto; display: table" src="<?php echo AddFile::addFiles('assets/images', 'icon', 'png', true); ?>" alt="logo">
                <form action="" method="post">
                    <br>

                    <div class="form-group">
                        <label for="acc_api_url">API URL</label>
                        <input type="text" class="form-control" id="acc_api_url" name="acc_api_url"
                               value="<?php echo get_option('acc_api_url'); ?>" placeholder="API URL" required>
                    </div>
                    <div class="form-group">
                        <label for="acc_merchant_id">Merchant Id</label>
                        <input type="text" class="form-control" id="acc_merchant_id" name="acc_merchant_id"
                               value="<?php echo get_option('acc_merchant_id'); ?>" placeholder="Merchant Id" required>
                    </div>
                    <div class="form-group">
                        <label for="acc_token">Authorization Token</label>
                        <input type="text" class="form-control" id="acc_token" name="acc_token"
                               value="<?php echo get_option('acc_token'); ?>" placeholder="Token" required>
                    </div>
                    <input id="submit" type="submit" name="save-credence" class="btn btn-success" value="Save Data">
                </form>
                <?php if (!empty($status)): ?>
                    <br>
                    <div class="alert alert-success" role="alert"><strong>Well done!</strong> You successfully saved
                        this data.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}
