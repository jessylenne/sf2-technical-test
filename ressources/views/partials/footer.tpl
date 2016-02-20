
{if isset($js_files) && sizeof($js_files)}
    {foreach from=$js_files item=file}
        <script type="text/javascript" src="{$file}"></script>
    {/foreach}
{/if}
</body>
</html>