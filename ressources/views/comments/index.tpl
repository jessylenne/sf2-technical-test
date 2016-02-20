<div class="container">
    <div class="row">
        <div class="col-xs-12 col-md-3">
            {include file="$tpl_dir./comments/partials/search.tpl"}
        </div>
        <div class="col-xs-12 col-md-8">
            {include file="$tpl_dir./partials/messages.tpl"}

            {if isset($comments) && is_array($comments) && sizeof($comments)}
                <h2>Mes commentaires ({sizeof($comments)})</h2>
                <div class="comments_list">
                {foreach from=$comments item=comment}
                    {include file="$tpl_dir./comments/partials/comment.tpl"}
                {/foreach}
                </div>
            {else}
                <div class="alert alert-info">Aucun commentaire encore saisit, commencez en recherchant un utilisateur Github!</div>
            {/if}
        </div>
    </div>
</div>