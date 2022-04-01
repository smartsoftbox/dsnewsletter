{*
*  @author Marcin Kubiak
*  @copyright  Smart Soft
*  @license    Commercial license
*  International Registered Trademark & Property of Smart Soft
*}

<div class="fixed-width-md" style="float:left;margin-right: 10px;">
    <select name="{$compare_name|escape:'htmlall':'UTF-8'}" class="{$class|escape:'htmlall':'UTF-8'} chosen fixed-width-xl" style="display: none;">
        <option value="0" {if $compare_value == 0} selected="selected" {/if}>bigger then</option>
        <option value="1" {if $compare_value == 1} selected="selected" {/if}>smaller then</option>
        <option value="2" {if $compare_value == 1} selected="selected" {/if}>eqaul to</option>
    </select>
</div>
<div class="input-group fixed-width-xl" style="float:left;margin-right: 10px;">
    <input id="{$name|escape:'htmlall':'UTF-8'}" type="text" name="{$name|escape:'htmlall':'UTF-8'}"
           style="max-width: 100px;"
           value="{$value|escape:'htmlall':'UTF-8'}" class="{$class|escape:'htmlall':'UTF-8'}" size="70">
</div>
<div style="clear: both"></div>
