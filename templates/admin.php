
<div class="wrap">
	<h1>Minter store dashboard</h1>

    <form method="post" action="options.php">
    <?php settings_errors();
    do_settings_sections( \MinterStore\Base\BaseController::getPluginName() );
    settings_fields(  \MinterStore\Base\BaseController::getPluginName().'_settings' );
    submit_button();?>
    </form>
</div>