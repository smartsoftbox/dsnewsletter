{*
*  @author Marcin Kubiak
*  @copyright  Smart Soft
*  @license    Commercial license
*  International Registered Trademark & Property of Smart Soft
*}
{foreach from=$result item=info}
  <tr class="row_hover">
    <td align="left" style="border-right: 1px solid #FFFFFF;border-bottom: 1px solid #FFFFFF;
                              background-color: rgb(248, 246, 234); padding: 0.4em 0.4em;">
        {$info.id|intval}
    </td>
    <td align="left" style="border-right: 1px solid #FFFFFF;border-bottom: 1px solid #FFFFFF;
                              background-color: rgb(248, 246, 234); padding: 0.4em 0.4em;">
      {$info.name|escape:'htmlall':'UTF-8'}
    </td>
    <td align="left" style="border-right: 1px solid #FFFFFF;border-bottom: 1px solid #FFFFFF;
                              background-color: rgb(248, 246, 234); padding: 0.4em 0.4em;">
      {$info.total|intval}
    </td>
    <td align="left" style="border-right: 1px solid #FFFFFF;border-bottom: 1px solid #FFFFFF;
                              background-color: rgb(248, 246, 234); padding: 0.4em 0.4em;">
      {$info.correct|intval}
    </td>
    <td align="left" style="border-right: 1px solid #FFFFFF;border-bottom: 1px solid #FFFFFF;
                              background-color: rgb(248, 246, 234); padding: 0.4em 0.4em;">
      {count($info.errors)|intval}
    </td>
  </tr>
  <tr>
    <td colspan="5" style="background-color: {if $info.errors|count} rgb(244, 77, 39) {else} rgb(107, 181, 0) {/if};
            padding: 0.4em 0.4em; color: rgb(255, 255, 255)">
      {if count($info.errors)}
          {l s='Error sending following emails' d='dsnewsletter'}
      {else}
          {l s='All emails sent correct.' d='dsnewsletter'}
      {/if}
    </td>
  </tr>
  <tr>
    <td colspan="5" style="background-color: rgb(255, 255, 255); padding: 0.4em 0.4em;">
      {if $info.errors|count}
        {foreach from=$info.errors item=email}
          {$email|escape:'htmlall':'UTF-8'} </br>
        {/foreach}
      {/if}
    </td>
  </tr>
{/foreach}
