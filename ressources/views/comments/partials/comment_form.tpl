<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-comment"></i> Ajouter un nouveau commentaire
    </div>
    <div class="panel-body">
        <form method="post" action="{$link->getPageLink('comments', ['add' => 1, 'user' => $profile.login])}">
            <div class="form-group">
                <label for="comment">Commentaire</label>
                <textarea name="comment" id="comment" class="form-control" rows="5" cols="10">{if isset($smarty.post.comment)}{$smarty.post.comment}{/if}</textarea>
            </div>
            <div class="alert alert-info">Choisissez de commenter l'utilisateur ou l'un de ses dépot en particulier</div>
            <div class="row">
                <div class="col-xs-12 text-center">
                    <input type="submit" class="btn btn-default" value="Ajouter" name="submitAddComment"/>
                </div>
            </div>
        </form>
    </div>
</div>