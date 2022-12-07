{*
*  @author Marcin Kubiak
*  @copyright  Smart Soft
*  @license    Commercial license
*  International Registered Trademark & Property of Smart Soft
*}

<div class="row">

  <div class="col-md-6">
    <div id="cpanel" class="col-md-12">
      <div class="panel">
        <div class="panel-heading">Quick links</div>
        <div class="separation"></div>
        <div class="icon">
          <a href="{$currentIndex|escape:'htmlall':'UTF-8'}&lists=1">
            <i class="icon-list"></i>
            <span>Lists</span>
          </a>
        </div>
        <div class="icon">
          <a href="{$currentIndex|escape:'htmlall':'UTF-8'}&templates=1">
            <i class="icon-list-alt"></i>
            <span>Templates</span>
          </a>
        </div>
        <div class="icon">
          <a href="{$currentIndex|escape:'htmlall':'UTF-8'}&newsletters=1">
            <i class="icon-envelope"></i>
            <span>Newsletters</span>
          </a>
        </div>
        <div class="icon">
          <a href="{$currentIndex|escape:'htmlall':'UTF-8'}&statistics=1">
            <i class="icon-tasks"></i>
            <span>Statistics</span>
          </a>
        </div>
        <div class="icon">
          <a href="{$currentIndex|escape:'htmlall':'UTF-8'}&settings=1">
            <i class="icon-gear"></i>
            <span>Settings</span>
          </a>
        </div>
      </div>
    </div>
    <div class="panel col-md-12">
      <div class="panel-heading">Start Guide</div>
      <ol class="rounded-list">
        <li style="background:#D6682;">
          <a href="http://storepresta.com/docs/advanced-newsletter/getting-started/module-setup/" target="_blank">
            Module Setup
          </a>
        </li>
        <li style="background:#D6682;">
          <a href="http://storepresta.com/docs/advanced-newsletter/getting-started/create-mailing-list/"
             target="_blank">
            Create mailing list
          </a>
        </li>
        <li style="background:#D6682;">
          <a href="http://storepresta.com/docs/advanced-newsletter/getting-started/add-customer-to-mailing-list/"
             target="_blank">
            Add customer to mailing list
          </a>
        </li>
        <li style="background:#D6682;">
          <a href="http://storepresta.com/docs/advanced-newsletter/getting-started/add-customer-group-to-mailing-list/"
             target="_blank">
            Add customer group to mailing list
          </a>
        </li>
        <li style="background:#D6682;">
          <a href="http://storepresta.com/docs/advanced-newsletter/getting-started/create-newsletter-template/"
             target="_blank">
            Create newsletter template
          </a>
        </li>
        <li style="background:#D6682;">
          <a href="http://storepresta.com/docs/advanced-newsletter/getting-started/create-newsletter/" target="_blank">
            Create newsletter
          </a>
        </li>
        <li style="background:#D6682;">
          <a href="http://storepresta.com/docs/advanced-newsletter/getting-started/create-newsletter-auto-future-sent/"
             target="_blank">
            Create newsletter (auto future sent)
          </a>
        </li>
        <li style="background:#D6682;">
          <a href="http://storepresta.com/docs/advanced-newsletter/getting-started/sent-newsletter-only-to-specfic-customer/"
             target="_blank">
            Send newsletter only to specfic customer
          </a>
        </li>
        <li style="background:#D6682;">
          <a href="http://storepresta.com/docs/advanced-newsletter/getting-started/mailing-statistics/" target="_blank">
            Mailing statistics
          </a>
        </li>
        <li style="background:#D6682;">
          <a href="http://storepresta.com/docs/advanced-newsletter/getting-started/queue-manger/"
             target="_blank">
            Queue manger
          </a>
        </li>
      </ol>
    </div>
    <div class="panel col-md-12">
      <div class="panel-heading">Video tutorials</div>
      <ol class="rounded-list">
        <li>
          <a target="_blank" href="http://www.youtube.com/watch?v=ra1K1TDhTIA">Module setup</a>
        </li>
        <li>
          <a target="_blank" href="http://www.youtube.com/watch?v=LJexUaeuFG4">Create mailing list</a>
        </li>
        <li>
          <a target="_blank" href="http://www.youtube.com/watch?v=WKz88jHxCBA&feature">Add / remove from queue</a>
        </li>
        <li>
          <a target="_blank" href="http://www.youtube.com/watch?v=oNpU5UmD6FM">Template customization</a>
        </li>
        <li>
          <a target="_blank" href="http://www.youtube.com/watch?v=eNgzLSN9_no">Automatic newsletter</a>
        </li>
      </ol>
    </div>
  </div>

  <div class="col-md-6">
    <div class="col-md-12">
      <div id="presentation" class="panel">
        <div class="panel-heading">Presentation -Last month</div>
        <div class="separation"></div>
        <div class="table_info">
          <div id="dashboard-stats"></div>
        </div>
      </div>
    </div>
    <div class="col-md-12">
      <div id="statistics" class="panel">
        <div class="panel-heading">Global statistics</div>
        <div class="separation"></div>
        <div class="table_info">
          <h5>Information</h5>
          <table class="table_info_details table" style="width:100%;">
            <colgroup>
              <col width="">
              <col width="80px">
            </colgroup>
            <tbody>
            <tr class="td_align_left">
              <td class="td_align_left">Customers</td>
              <td class="">{$customers|escape:'htmlall':'UTF-8'}</td>
              <td class="td_align_left">Subscribers</td>
              <td class="">{$subscribers|escape:'htmlall':'UTF-8'}</td>
            </tr>
            <tr class="tr_odd">
              <td class="td_align_left">Lists</td>
              <td class="">{$lists|escape:'htmlall':'UTF-8'}</td>
              <td class="td_align_left">Newsletters</td>
              <td class="">{$newsletters|escape:'htmlall':'UTF-8'}</td>
            </tr>
            <tr class="td_align_left">
              <td class="td_align_left">Automatic</td>
              <td class="">{$automatic|escape:'htmlall':'UTF-8'}</td>
              <td class="td_align_left">Queue</td>
              <td class="">{$queue|escape:'htmlall':'UTF-8'}</td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-md-12">
      <div class="panel">
        <div class="panel-heading" id="current_obj">Support</div>
        <div class="">
            <a target="_blank" href="https://addons.prestashop.com/pl/contact-us?id_product=8968"> Please contact us using
              prestashop contact form. </a>
        </div>
      </div>
    </div>
    <div class="col-md-12">
      <div class="panel">
        <div class="panel-heading">Professional Newsletter</div>
        <div class="">
          If you like professional newsletter please rate it.
          <img style="margin-left:5px;" src="../modules/dsnewsletter/views/img/five-star.png">
        </div>
      </div>
    </div>
  </div>
</div>
