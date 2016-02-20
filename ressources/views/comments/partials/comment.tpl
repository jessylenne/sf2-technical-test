<div class="row comment">
    <div class="col-xs-4">
        <ul class="list-group">
            <li class="list-group-item"><i class="fa fa-calendar"></i> {$comment->getDate('d/m/Y')}</li>
            <li class="list-group-item"><i class="fa fa-user"></i> <a href="{$link->getPageLink('comments', ['user' => $comment->getUsername()])}">{$comment->getUsername()}</a></li>
            {if $comment->getRepository() !== "all"}
            <li class="list-group-item"><i class="fa fa-archive"></i> {$comment->getRepository()}</li>
            {/if}
        </ul>
    </div>
    <div class="col-xs-8">
        <div class="well">{$comment->getComment()}</div>
    </div>
</div>