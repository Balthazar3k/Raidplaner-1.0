{if !empty($article) }
    <ul class="list-group">
        {foreach $article as $i}
        <li class="list-group-item" style="padding-left: 0px; padding-right: 0px;">
            <div class="col-lg-2">
                <img class="img-thumbnail" src="
                     {if file_exists($i.article_image)}
                         {$i.article_image}
                     {elseif file_exists($i.category_image)}
                         {$i.category_image}
                     {else}
                         include/angelo.b3k/images/placeholder.png
                     {/if}
                ">
            </div>
            <div class="col-lg-5">
                <h3 style="margin-top: 5px;">{$i.article_name} <b class="small">- {$i.category_name}</b></h3>
                <p>  
                    <hr>
                    {$i.article_description|truncate:128}
                </p>
            </div>
            <div class="col-lg-5">
                <ul class="list-group" style="margin-bottom: 0px;">
                    <li class="list-group-item list-group-item-info text-center"><b>{$i.article_grossprice|price}</b> - {$i.article_amount} {$i.unit_unit}</li>
                    {if $i.article_discount != 0}
                        <li class="list-group-item list-group-item-success text-center">
                            <b>Rabatt: {$i.article_discount}% | <span style="text-decoration: line-through;">{$i.article_taxnetprice|price}</span></b>
                        </li>
                    {/if}
                    <li class="list-group-item">
                        <form id="standart" action="index.php?shop-ajax-shoppingCart">
                            <input type="hidden" name="article_id" value="{$i.article_id}" />
                            <input type="hidden" name="article_amount" value="{$i.article_amount}" />
                            <input type="hidden" name="article_grossprice" value="{$i.article_grossprice}" />
                            <div class="input-group">
                                <div class="input-group-btn">
                                    <a data-amount="+{$i.article_amount}" class="btn btn-default" href="#">&nbsp;<i class="fa fa-plus-circle"></i></a>
                                    <a data-amount="-{$i.article_amount}" class="btn btn-default" href="#"><i class="fa fa-minus-circle"></i>&nbsp;</a>
                                </div>
                                <input type="text" class="form-control text-center" name="user_amount" style="padding: 0px;" value="{$i.article_amount}">
                                <span class="input-group-addon">{$i.unit_short}</span>
                                <div class="input-group-btn">
                                    <button type="submit" class="btn btn-success" href="">&nbsp;<i class="fa fa-shopping-cart"></i>&nbsp;</button>
                                </div>
                            </div>
                        </form>
                    </li>
                </ul>
            </div>
            <br style="clear: both;" />
        </li>
        {/foreach}
    </ul>
{else}
    <div class="alert alert-info">
        <i class="fa fa-info-circle fa-2x pull-left"></i> 
        <p class="pull-left">
            Es wurden Leider keine Artikel unter diesem Suchbegriff gefunden.
        </p>
        <br style="clear: both;" />
    </div>
{/if}
