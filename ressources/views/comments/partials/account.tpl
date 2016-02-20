<div class="row">
    <div class="col-xs-12 col-md-3 search_account_avatar">
        {if strlen($account.avatar_url)}
            <img src="{$account.avatar_url}" alt="{$account.login}" class="img-responsive img-circle"/>
        {else}
            <i class="fa fa-user"></i>
        {/if}
    </div>
    <div class="col-xs-12 col-md-8 search_account_details">
        <h3>{$account.login}</h3>
        <div class="btn-group">
            <a class="btn btn-xs btn-info" href="{$link->getPageLink('comments', ['user' => $account.login])}"><i class="fa fa-comment"></i> Commenter</a>
            <a class="btn btn-xs btn-default" href="{$account.html_url}" target="_blank">Voir son profil</a>
        </div>
    </div>
</div>