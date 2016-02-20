<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-comment"></i> Ajouter un nouveau commentaire
    </div>
    <div class="panel-body">
        <form method="post" action="{$link->getPageLink('comments', ['add' => 1, 'user' => $profile.login])}">
            {* @todo csrf *}
            <div class="form-group">
                <label for="comment">Commentaire</label>
                <textarea name="comment" id="comment" class="form-control" rows="5" cols="10">{if isset($smarty.post.comment)}{$smarty.post.comment|sanitize}{/if}</textarea>
            </div>
            <div class="alert alert-info">Choisissez de commenter l'utilisateur ou l'un de ses dépot en particulier</div>
            <div class="row">
                {if $repositories && sizeof($repositories)}
                <div class="col-xs-12 col-md-1">
                    <label for="repository">Dépot</label>
                </div>
                <div class="col-xs-12 col-md-9">
                    <select class="form-control" name="repository" id="repository">
                        <option data-for="all" value="all">Ne pas commenter un dépot en particulier</option>
                        {foreach from=$repositories item=repository}
                            <option data-for="{$repository.id}" value="{$repository.name}">{$repository.name}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-xs-12 col-md-2 text-center">
                    {else}
                    <div class="col-xs-12 text-center">
                        {/if}
                        <input type="submit" class="btn btn-default" value="Ajouter" name="submitAddComment"/>
                    </div>
                </div>
        </form>
    </div>
    {if sizeof($repositories)}
        <div class="panel-footer" id="comment_form_panel_footer">
            {foreach from=$repositories item=repository}
                <div class="repository_detail" id="repository_detail_{$repository.id}">
                    <h4>{$repository.full_name}</h4>

                    <div class="row">
                        <div class="col-md-9">
                            <p>
                                {if $repository.description}
                                    {$repository.description}
                                {else}
                                    Aucune description fournie
                                {/if}
                            </p>
                        </div>
                        <div class="col-md-3">
                            <a href="{$repository.html_url}" target="_blank" class="btn btn-default"><i class="fa fa-github"></i> Voir sur github</a>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    {/if}
</div>