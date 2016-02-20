<div class="container">
    <h1>Rechercher un profil Github</h1>

    <div class="row">
        <div class="col-xs-12 col-md-3">
            {include file="$tpl_dir./comments/partials/search.tpl"}
        </div>
        <div class="col-xs-12 col-md-8">
            {include file="$tpl_dir./partials/messages.tpl"}

            {if $total_count && $accounts}
                <h2>{$total_count} résultat{if $total_count > 1}s{/if}</h2>
                <div class="row">
                {foreach from=$accounts item=account name=accounts}
                    {if $smarty.foreach.accounts.index && $smarty.foreach.accounts.index %2 == 0}</div><div class="row">{/if}
                    <div class="col-xs-12 col-md-6">
                        <div class="search_account">
                        {include file="$tpl_dir./comments/partials/account.tpl"}
                        </div>
                    </div>
                {/foreach}
                </div>
            {/if}
        </div>
    </div>

</div>