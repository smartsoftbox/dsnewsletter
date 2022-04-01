{*
*  @author Marcin Kubiak
*  @copyright  Smart Soft
*  @license    Commercial license
*  International Registered Trademark & Property of Smart Soft
*}

<a class="pointer" id="details_{$params.action|escape:'htmlall':'UTF-8'}_{$id|escape:'htmlall':'UTF-8'}" title="{$action|escape:'htmlall':'UTF-8'}" onclick="display_action_details('{$id|escape:'htmlall':'UTF-8'}', '{$controller|escape:'htmlall':'UTF-8'}', '{$token|escape:'htmlall':'UTF-8'}', '{$params.action|escape:'htmlall':'UTF-8'}', {$json_params|escape|escape:'htmlall':'UTF-8'}); return false">
	<img src="../img/admin/more.png" alt="{$action|escape:'htmlall':'UTF-8'}" />
</a>
