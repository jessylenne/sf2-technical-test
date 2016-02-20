<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{$meta_title}</title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {if isset($css_files) && sizeof($css_files)}
        {foreach from=$css_files item=file}
            <link href="{$file}" media="all" rel="stylesheet"/>
        {/foreach}
    {/if}
</head>
<body>
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">{$app.name}</a>
            </div>

            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li><a href="{$link->getPageLink('comments')}">Mes commentaires</a></li>
                </ul>
                {if $user}
                    <ul class="nav navbar-nav navbar-right">
                        <li><a href="{$link->getPageLink('auth', 'logout')}">Déconnexion</a></li>
                    </ul>
                    <form class="navbar-form navbar-right search-form" role="search" action="{$link->getPageLink('comments')}" method="post">
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Rechercher" name="search" id="headsearch">
                        </div>
                        <button type="submit" class="btn btn-default" name="submitSearchAccount"><i class="fa fa-search"></i></button>
                    </form>
                {/if}
            </div>
        </div>
    </nav>