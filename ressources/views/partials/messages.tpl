{if isset($errors) && $errors}
    {foreach from=$errors item=error}
        <div class="alert alert-warning">{$error}</div>
    {/foreach}
{/if}
{if isset($success) && $success}
    {foreach from=$success item=succes}
        <div class="alert alert-success">{$succes}</div>
    {/foreach}
{/if}