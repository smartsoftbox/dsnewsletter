{*
*  @author Marcin Kubiak
*  @copyright  Smart Soft
*  @license    Commercial license
*  International Registered Trademark & Property of Smart Soft
*}

<div class="row">
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-2 col-md-3">
                <div class="list-group" id="links">
                    <a href="{$currentIndex|escape:'html':'UTF-8'}&lists=1" class="list-group-item">
                        <i class="icon-list"></i>
                        <span>Lists</span><span class="badge badge-success">1</span>
                    </a>
                    <a href="{$currentIndex|escape:'htmlall':'UTF-8'}&templates=1" class="list-group-item">
                        <i class="icon-list-alt"></i>
                        <span>Templates</span><span class="badge badge-success">2</span>
                    </a>
                    <a href="{$currentIndex|escape:'htmlall':'UTF-8'}&newsletters=1" class="list-group-item">
                        <i class="icon-envelope"></i>
                        <span>Newsletters</span><span class="badge badge-success">3</span>
                    </a>
                    <a href="{$currentIndex|escape:'htmlall':'UTF-8'}&statistics=1" class="list-group-item">
                        <i class="icon-tasks"></i>
                        <span>Statistics</span>
                    </a>
                    <a href="{$AdminCustomerController|escape:'htmlall':'UTF-8'}" class="list-group-item">
                        <i class="icon-user"></i>
                        <span>Customers</span>
                    </a>
                    <a href="{$currentIndex|escape:'htmlall':'UTF-8'}&settings=1" class="list-group-item">
                        <i class="icon-gears"></i>
                        <span>Settings</span>
                    </a>
                    <a id="link-docs" href="{$currentIndex|escape:'htmlall':'UTF-8'}&docs=1" class="list-group-item">
                        <i class="icon-book"></i>
                        <span>Documentation</span>
                    </a>
                    <a href="{$currentIndex|escape:'htmlall':'UTF-8'}&support=1" class="list-group-item">
                        <i class="icon-question"></i>
                        <span>Support</span>
                    </a>
                </div>
            </div>
            <div class="form-horizontal col-lg-10">
                <div class="list-group">
                    <div class='product-tab-content'>
