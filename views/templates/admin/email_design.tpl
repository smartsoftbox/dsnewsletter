{*
*  @author Marcin Kubiak
*  @copyright  Smart Soft
*  @license    Commercial license
*  International Registered Trademark & Property of Smart Soft
*}

<ul id="my-nav-pills" class="nav nav-pills">
    <li class="active"><a href="#editor" data-toggle="tab">Edit HTML version</a></li>
    <li><a href="#view-html" data-toggle="tab">View HTML version</a></li>
    <li><a href="#text" data-toggle="tab">View/Edit TXT version</a></li>
</ul>
<div class="tab-content">
    <div class="tab-pane" id="view-html">
        <div class="block-mail" >
            <div class="mail-form">
                <div class="thumbnail mail-html-frame">
                    {* This is html preview for admin only *}
                    {$html_content_with_tags}
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="text">
        <div class="block-mail" >
            <div class="mail-form">
                <div>
                    <textarea class="rte noEditor" id="plaintext" name="plaintext">
                        {$text_content|escape:'html':'UTF-8'}
                    </textarea>
                </div>
{*                <span id="generate" class="btn-tag btn btn-default">auto generate</span>*}
            </div>
        </div>
    </div>
    <div class="tab-pane active" id="editor">
        <div id="app-mail">
            <div id="root"></div>
        </div>
    </div>

    <script type="module" crossorigin src="../modules/dsnewsletter/views/js/easy.js"></script>
    <link rel="modulepreload" href="../modules/dsnewsletter/views/js/vendor.js">
    <link rel="stylesheet" href="../modules/dsnewsletter/views/css/easy.css">

    <script>
        let id_template = '{$id_template|escape:'html':'UTF-8'}';

        document.addEventListener("DOMContentLoaded", function(event) {

            document.getElementById('dstemplate_form_submit_btn').addEventListener('click', function(e){
                e.preventDefault();
                document.getElementById('addEasyEmail').click();
                document.getElementById('dstemplate_form').submit();
            });

            let sumbitAndStay = document.querySelector('button[name="submitAddTemplateAndStay"]');
            sumbitAndStay.addEventListener('click', function (e) {
                e.preventDefault();
                document.getElementById('addEasyEmail').click();

                let input = document.querySelector('input[name="submitAddDstemplate"]');
                input.setAttribute('name', sumbitAndStay.getAttribute('name'));
                document.getElementById('dstemplate_form').submit();
            });

        });
    </script>
</div>
{*Fix for design display*}
<div style="clear: both; width: 100%" ></div>