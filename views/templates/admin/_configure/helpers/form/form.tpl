{*
*  @author Marcin Kubiak
*  @copyright  Smart Soft
*  @license    Commercial license
*  International Registered Trademark & Property of Smart Soft
*}

{extends file="helpers/form/form.tpl"}

{block name="input"}
    {if $input.type == 'langs'}
        <table cellspacing="0" cellpadding="0" class="table" style="width:28em;">
            <tr>
                <th>
                    <input type="checkbox" name="checkme" id="checkme" class="noborder" onclick="checkDelBoxes(this.form, 'langBox[]', this.checked)" />
                </th>
                <th>{l s='ID' mod='dsnewsletter'}</th>
                <th>{l s='Language name' mod='dsnewsletter'}</th>
            </tr>
            {foreach $input.values as $key => $lang}
                <tr {if $key %2}class="alt_row"{/if}>
                    <td>
                        {assign var=id_checkbox value=langBox|cat:'_'|cat:$lang['id_lang']}
                        <input type="checkbox" name="langBox[]" class="langBox" id="{$id_checkbox|escape:'htmlall':'UTF-8'}" value="{$lang['id_lang']|escape:'htmlall':'UTF-8'}" {if $fields_value[$id_checkbox]}checked="checked"{/if} />
                    </td>
                    <td>{$lang['id_lang']|escape:'htmlall':'UTF-8'}</td>
                    <td><label for="{$id_checkbox|escape:'htmlall':'UTF-8'}" class="t">{$lang['name']|escape:'htmlall':'UTF-8'}</label></td>
                </tr>
            {/foreach}
        </table>
    {elseif $input.type == 'lists'}
        <table cellspacing="0" cellpadding="0" class="table" style="width:28em;">
            <tr>
                <th>
                    <input type="checkbox" name="checkme" id="checkme" class="noborder" onclick="checkDelBoxes(this.form, 'listsBox[]', this.checked)" />
                </th>
                <th>{l s='List name' mod='dsnewsletter'}</th>
            </tr>
            {if $input.values}
                {foreach $input.values as $key => $list}
                    <tr {if $key %2}class="alt_row"{/if}>
                        <td>
                            {assign var=id_checkbox value=listsBox|cat:'_'|cat:$list['id_dslist']}
                            <input type="checkbox" name="listsBox[]" class="listsBox" id="{$id_checkbox|escape:'htmlall':'UTF-8'}" value="{$list['id_dslist']|escape:'htmlall':'UTF-8'}" {if $fields_value[$id_checkbox]}checked="checked"{/if} />
                        </td>
                        <td><label for="{$id_checkbox|escape:'htmlall':'UTF-8'}" class="t">{$list['name']|escape:'htmlall':'UTF-8'}</label></td>
                    </tr>
                {/foreach}
            {else}
                <tr>
                    <td colspan="2" style="color:red;"><a href="{$fields_value['newListLink']|escape:'htmlall':'UTF-8'}">Please create list first.</a></td>
                </tr>
            {/if}
        </table>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
