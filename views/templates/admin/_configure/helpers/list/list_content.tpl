{*
*  @author Marcin Kubiak
*  @copyright  Smart Soft
*  @license    Commercial license
*  International Registered Trademark & Property of Smart Soft
*}

{extends file="helpers/list/list_content.tpl"}

{block name="td_content"}
    {if isset($tr.$key)}
        {if isset($params.type) && $params.type == 'lang'}
            {foreach $tr.$key AS $id_lang}
                <img src="../img/l/{$id_lang|escape:'htmlall':'UTF-8'}.jpg"
                     onclick="document.location = '{$current_index|escape:'html':'UTF-8'}&amp;{$identifier|escape:'html':'UTF-8'}={$tr.$identifier|escape:'html':'UTF-8'}{if $view}&amp;view{else}&amp;update{/if}{$table|escape:'html':'UTF-8'}{if $page > 1}&amp;page={$page|intval}{/if}&amp;template_id_lang={$id_lang|escape:'html':'UTF-8'}&amp;token={$token|escape:'html':'UTF-8'}'" />
            {/foreach}
        {elseif isset($params.type) && $params.type == 'lists'}
            {if is_array($tr.$key)}
                {foreach $tr.$key AS $color}
                    <span style="background:{$color|escape:'htmlall':'UTF-8'};width:20px;height:20px;float:left;margin-right:1px;"></span>
                {/foreach}
            {else}
                <span style="background:{$color|escape:'htmlall':'UTF-8'};width:20px;height:20px;float:left;margin-right:1px;"></span>
            {/if}
        {elseif isset($params.type) && $params.type == 'subscribe'}
            <span class="btn-group-action">
                <span class="btn-group">
                    <a class="btn btn-default" href="{$tr.$key.url|escape:'htmlall':'UTF-8'}">
                        <i class="icon-search-plus"></i>&nbsp;{$tr.$key.number|escape:'htmlall':'UTF-8'}
                    </a>
                </span>
            </span>
        {else}
            {$smarty.block.parent}
        {/if}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
