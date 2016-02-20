<div class="container">
    <div class="row">
        <div class="col-xs-12 col-md-3">
            <div class="well">
                {if strlen($profile.avatar_url)}
                    <img src="{$profile.avatar_url}" alt="{$profile.login}" class="img-responsive img-circle"/>
                {else}
                    <i class="fa fa-user"></i>
                {/if}
                <h1 class="text-center">{$profile.name}</h1>
                <ul class="list-group">
                    {if $profile.bio}
                        <li class="list-group-item">{$profile.bio}</li>
                    {/if}
                    {if $profile.location}
                    <li class="list-group-item"><i class="fa fa-map-marker"></i> {$profile.location}</li>
                    {/if}
                    {if $profile.blog}
                    <li class="list-group-item"><i class="fa fa-rss"></i> {$profile.blog}</li>
                    {/if}
                    <li class="list-group-item">{$profile.public_repos} dépot{if $profile.public_repos > 1}s{/if} public{if $profile.public_repos > 1}s{/if}</li>
                    <li class="list-group-item">{$profile.public_gists} gist{if $profile.public_gists > 1}s{/if} public{if $profile.public_gists > 1}s{/if}</li>
                </ul>
            </div>
        </div>
        <div class="col-xs-12 col-md-9">
            {include file="$tpl_dir./partials/messages.tpl"}

            {include file="$tpl_dir./comments/partials/comment_form.tpl"}

            {if !is_array($comments) || !sizeof($comments)}
                <div class="alert alert-info">Aucun commentaire encore saisit pour ce profil</div>
            {else}
                <h2><i class="fa fa-comment"></i> {if $comments}{sizeof($comments)}{/if} Commentaire{if sizeof($comments) > 1}s{/if}</h2>
                <div class="comments_list">
                    {foreach from=$comments item=comment}
                        {include file="$tpl_dir./comments/partials/comment.tpl"}
                    {/foreach}
                </div>
            {/if}
        </div>
    </div>
</div>