<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-search"></i> Rechercher
    </div>
    <div class="panel-body">
        <form method="post" action="{$link->getPageLink('comments')}" class="search-form">
            <div class="form-group">
                <label for="search">Identifiant</label>
                <input type="text" name="search" id="search" class="form-control"/>
            </div>
            <div class="text-right">
                <button class="btn btn-default" name="submitSearchAccount"><i class="fa fa-search"></i> Rechercher</button>
            </div>
        </form>
    </div>
</div>