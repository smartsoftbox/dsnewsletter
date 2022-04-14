{*
*  @author Marcin Kubiak
*  @copyright  Smart Soft
*  @license    Commercial license
*  International Registered Trademark & Property of Smart Soft
*}

<div class="panel kpi-container">
    <div class="row">
        <div class="col-sm-12 col-lg-3">
            <div id="select-newsletter-wrapper" class="col-sm-12 col-lg-12">
                <select name="select_newsletter" class="chosen fixed-width-xl"
                        id="select_newsletter" style="display: none;">
                    <option value="#">
                        Please select newsletter
                    </option>
                    {foreach from=$newsletters item=newsletter}
                        <option value="{$base|escape:'html':'UTF-8'}&id_newsletter={$newsletter.id_dsnewsletter|escape:'intval':'UTF-8'}"
                                {if $newsletter.id_dsnewsletter === $id_newsletter}selected="selected"{/if}>
                            {$newsletter.name|escape:'html':'UTF-8'}
                        </option>
                    {/foreach}
                </select>
            </div>
            <div class="col-sm-6 col-lg-12">
                <div class="box-stats label-tooltip color1">
                    <div class="kpi-content">
                        <i class="icon-envelope-alt"></i>
                        <span class="title">Total Send</span>
                        <span class="subtitle">30 days</span>
                        <span class="value">{$total_sent_number|escape:'intval':'UTF-8'}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-12">
                <div class="box-stats label-tooltip color1">
                    <div class="kpi-content">
                        <i class="icon-folder-open-alt"></i>
                        <span class="title">Total Open</span>
                        <span class="subtitle">30 days</span>
                        <span class="value">{$total_open|escape:'intval':'UTF-8'}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-12">
                <div id="box-average-order" data-toggle="tooltip" class="box-stats label-tooltip color3">
                    <div class="kpi-content">
                        <i class="icon-link"></i>
                        <span class="title">Total Click</span>
                        <span class="subtitle">30 days</span>
                        <span class="value">{$total_click|escape:'intval':'UTF-8'}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-12">
                <div id="box-net-profit-visitor" data-toggle="tooltip" class="box-stats label-tooltip color4"
                     data-original-title="">
                    <div class="kpi-content">
                        <i class="icon-exclamation-sign"></i>
                        <span class="title">Total Failed</span>
                        <span class="subtitle">30 days</span>
                        <span class="value">{$total_failed|escape:'intval':'UTF-8'}</span>
                    </div>
                </div>
            </div>
        </div>
        <div id="chart" class="col-sm-12 col-lg-9 chart with-transitions">
            <svg width="100%" height="100%"></svg>
        </div>
    </div>
</div>
<script>
    var stats = {$stats};
</script>