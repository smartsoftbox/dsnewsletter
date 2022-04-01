{*
*  @author Marcin Kubiak
*  @copyright  Smart Soft
*  @license    Commercial license
*  International Registered Trademark & Property of Smart Soft
*}

<div class="input-group fixed-width-xl" style="float:left;margin-right: 10px;">
  <span class="input-group-addon">day</span>
  <input id="{$from_name|escape:'htmlall':'UTF-8'}" type="text" name="{$from_name|escape:'htmlall':'UTF-8'}"
         style="max-width: 250px;"
         value="{$from_value|escape:'htmlall':'UTF-8'}" class="{$class|escape:'htmlall':'UTF-8'}" size="70">

</div>
<div class="input-group fixed-width-xl" style="float:left;margin-right: 10px;">
  <span class="input-group-addon">hour</span>
  <input id="{$to_name|escape:'htmlall':'UTF-8'}" type="text" name="{$to_name|escape:'htmlall':'UTF-8'}"
         style="max-width: 250px;"
         value="{$to_value|escape:'htmlall':'UTF-8'}" class="{$class|escape:'htmlall':'UTF-8'}" size="70">
</div>
<div style="clear: both"></div>
