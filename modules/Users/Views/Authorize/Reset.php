<div class='row-responsive'>
    <h2 style="margin-top: 25px; padding-bottom: 10px; border-bottom: 1px solid #FFF;"><?= __d('users', 'Password Reset'); ?></h2>
</div>

<div class="row">
    <div style="margin-top: 50px" class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-primary" >
            <div class="panel-heading">
                <div class="panel-title"><?= __d('users', 'Password Reset'); ?></div>
            </div>
            <div class="panel-body">
                <?= Session::getMessages(); ?>

                <form action="<?= site_url('password/reset'); ?>" method='POST' role="form">

                <div class="form-group">
                    <p><input type="text" name="email" id="email" class="form-control input-lg col-xs-12 col-sm-12 col-md-12" placeholder="<?= __d('users', 'Insert the current E-Mail'); ?>"><br><br></p>
                </div>
                <div class="form-group">
                    <p><input type="password" name="password" id="password" class="form-control input-lg col-xs-12 col-sm-12 col-md-12" placeholder="<?= __d('users', 'Insert the new Password'); ?>"><br><br></p>
                </div>
                <div class="form-group">
                    <p><input type="password" name="password_confirmation" id="password_confirmation" class="form-control input-lg col-xs-12 col-sm-12 col-md-12" placeholder="<?= __d('users', 'Verify the new Password'); ?>"><br><br></p>
                </div>
                <?php if (Config::get('reCaptcha.active') === true) { ?>
                <div class="row">
                    <div class="row pull-right" style="margin-top: 10px; margin-right: 0;">
                        <div id="captcha" style="width: 304px; height: 78px;"></div>
                    </div>
                    <div class="clearfix"></div>
                    <hr>
                </div>
                <?php } ?>
                <div class="row" style="margin-top: 22px;">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <input type="submit" name="submit" class="btn btn-success col-sm-4 pull-right" value="<?= __d('users', 'Send'); ?>">
                    </div>
                </div>

                <input type="hidden" name="_token" value="<?= csrf_token(); ?>" />
                <input type="hidden" name="token" value="<?= $token; ?>" />

                </form>
            </div>
        </div>
    </div>
</div>

<?php if (Config::get('reCaptcha.active') === true) { ?>

<script type="text/javascript">

var captchaCallback = function() {
    grecaptcha.render('captcha', {'sitekey' : '<?= Config::get("reCaptcha.siteKey"); ?>'});
};

</script>

<script src="//www.google.com/recaptcha/api.js?onload=captchaCallback&render=explicit&hl=<?= Language::code(); ?>" async defer></script>

<?php } ?>
