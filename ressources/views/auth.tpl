<div class="container">
    <div class="row">
        <div class="col-xs-12 col-md-6 col-md-offset-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-user"></i> Connexion
                </div>
                <div class="panel-body">
                    <form method="post">
                        <div class="text-center">
                            <h2 class="panel-title-icon"><i class="fa fa-key"></i></h2>
                        </div>
                        {include file="$tpl_dir./partials/messages.tpl"}
                        <div class="form-group">
                            <label for="username">E-mail</label>
                            <input type="email" name="username" id="username" class="form-control text-center" required/>
                        </div>
                        <div class="form-group">
                            <label for="password">Mot de passe</label>
                            <input type="password" name="password" id="password" class="form-control text-center" required/>
                        </div>
                        <div id="authMessages"></div>
                        <div class="text-center">
                            <button class="btn btn-default" name="submitLogin">
                                <i class="fa fa-key"></i> Connexion
                            </button>
                            <button class="btn btn-info" name="submitSubscribe">
                                <i class="fa fa-user"></i> Inscription
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>