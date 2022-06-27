{*
*  @author Marcin Kubiak
*  @copyright  Smart Soft
*  @license    Commercial license
*  International Registered Trademark & Property of Smart Soft
*}

{if $is_16}
    <p class="alert alert-success">{$message|escape:'html':'UTF-8'}</p>
{else}

{extends file='page.tpl'}

{block name='page_header_container'}
{/block}

{block name='page_content_container'}
    <p class="alert alert-success">{$message|escape:'html':'UTF-8'}</p>
{/block}

{/if}