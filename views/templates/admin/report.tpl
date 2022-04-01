{*
*  @author Marcin Kubiak
*  @copyright  Smart Soft
*  @license    Commercial license
*  International Registered Trademark & Property of Smart Soft
*}
<div class="bootstrap">
    <div class="panel">
        <h3>{l s='Sending report' mod='dsnewsletter'}: {$name|escape:'htmlall':'UTF-8'}</h3>
        <table cellspacing="0" cellpadding="0" style="min-width: 400px; margin-bottom: 10px;" class="table  template">
            <thead>
                <tr class="nodrag nodrop">
                    <th class="center"><span class="title_box">{l s='Id' mod='dsnewsletter'}</span>
                    <th class="center"><span class="title_box">{l s='Name' mod='dsnewsletter'}</span>
                    <th class="center"><span class="title_box">{l s='Total' mod='dsnewsletter'}</span>
                    <th class="center"><span class="title_box">{l s='Sent' mod='dsnewsletter'}</span>
                    <th class="center"><span class="title_box">{l s='Faild' mod='dsnewsletter'}</span>
                </tr>
            </thead>
            <tbody>
                <tr class="row_hover">
                    <td class="center" style="padding: 5px;"> {$id|escape:'htmlall':'UTF-8'} </td>
                    <td class="center"> {$name|escape:'htmlall':'UTF-8'} </td>
                    <td class="center"> {$total|escape:'htmlall':'UTF-8'} </td>
                    <td class="center"> {$correct|escape:'htmlall':'UTF-8'} </td>
                    <td class="center"> {$error|escape:'htmlall':'UTF-8'} </td>
                </tr>
                {if $error}
                    <tr>
                       <td colspan="5" style="text-align:center;padding:10px 5px;">
                           {l s='There was a problem sending following emails' mod='dsnewsletter'}
                       </td>
                    </tr>
                    <tr>
                       <td colspan="5">
                           <p style="overflow:hidden; color: red; width: 100%; height: 150px; border: none; padding: 0; text-align:left;">
                               {foreach from=$errors item=error name=error} {$error|escape:'htmlall':'UTF-8'}
                                 </br>
                               {/foreach}
                           </p>
                       </td>
                    </tr>
                {/if}
            </tbody>
        </table>
    </div>
</div>