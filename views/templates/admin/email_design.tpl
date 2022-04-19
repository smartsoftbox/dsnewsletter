{*
*  @author Marcin Kubiak
*  @copyright  Smart Soft
*  @license    Commercial license
*  International Registered Trademark & Property of Smart Soft
*}

<ul id="my-nav-pills" class="nav nav-pills">
    <li class="active"><a href="#editor" data-toggle="tab">Edit HTML version</a></li>
    <li><a href="#tags-tab" data-toggle="tab">Tags</a></li>
    <li><a href="#view-html" data-toggle="tab">View HTML version</a></li>
    <li><a href="#text" data-toggle="tab">View/Edit TXT version</a></li>
</ul>
<div class="tab-content">
    <div class="tab-pane" id="view-html">
        <div class="block-mail" >
            <div class="mail-form">
                <div class="thumbnail mail-html-frame">
                    {$html_content_with_tags}
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="tags-tab">
        <div class="block-mail" >
            <div class="mail-form thumbnail">
                <a id="generate_new_tags" class="btn btn-default">Generate new product tags</a>
                <a id="generate_featured_tags" class="btn btn-default">Generate featured product tags</a>
                <div id="tags">
                    {foreach $tags as $tag}
                        <a class="btn-tag btn btn-default"  data-clipboard-text="{$tag|escape:'html':'UTF-8'}">
                            {$tag|escape:'html':'UTF-8'}
                        </a>
                    {/foreach}
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
                <span id="generate" class="btn-tag btn btn-default">auto generate</span>
            </div>
        </div>
    </div>
    <div class="tab-pane active" id="editor">
        <div id="app-mail"></div>
    </div>
    <script src="http://editor.unlayer.com/embed.js"></script>
    <script>
        let placeholder = '{$placeholder|escape:'html':'UTF-8'}'; // image product url for tags
        let id_template = '{$id_template|escape:'html':'UTF-8'}';

        document.addEventListener("DOMContentLoaded", function(event) {
            // Your code to run since DOM is loaded and ready
            unlayer.init({
                id: 'app-mail',
                displayMode: 'email',
            });

            unlayer.registerCallback('image', function(file, done) {
                var data = new FormData()
                data.append('images', file.attachments[0])

                fetch(urlJson + '&id_template=' + id_template + '&action=uploadImage', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: data
                }).then(response => {
                    // Make sure the response was valid
                    if (response.status >= 200 && response.status < 300) {
                        return response
                    } else {
                        var error = new Error(response.statusText)
                        error.response = response
                        throw error
                    }
                }).then(response => {
                    return response.json()
                }).then(data => {
                    // Pass the URL back to Unlayer to mark this upload as completed
                    done({ progress: 100, url: data.filelink })
                })
            });

            if(document.getElementById('design').value) {
                unlayer.loadDesign(JSON.parse(document.getElementById('design').value));
            }

            document.getElementById('dstemplate_form_submit_btn').addEventListener('click', function(e){
                e.preventDefault();
                unlayer.exportHtml(function(data) {
                    document.getElementById('design').value = JSON.stringify(data.design); // design json
                    document.getElementById('html').value = data.html; // final html
                    document.getElementById('plain-text').value = document.getElementById('plaintext').value; // final text
                    document.getElementById('dstemplate_form').submit();
                });
            });

            let sumbitAndStay = document.querySelector('button[name="submitAddTemplateAndStay"]');
            sumbitAndStay.addEventListener('click', function (e) {
                e.preventDefault();
                unlayer.exportHtml(function(data) {
                    document.getElementById('design').value = JSON.stringify(data.design); // design json
                    document.getElementById('html').value = data.html; // final html
                    document.getElementById('plain-text').value = document.getElementById('plaintext').value; // final text

                    let input = document.querySelector('input[name="submitAddDstemplate"]');
                    input.setAttribute('name', sumbitAndStay.getAttribute('name'));
                    document.getElementById('dstemplate_form').submit();
                }); // run first
            });

            let generate = document.getElementById('generate');
            generate.addEventListener('click', function(e){
                e.preventDefault();
                unlayer.exportPlainText(function(data) {
                    document.getElementById('plaintext').value = data.text; // final text
                })
            });
        });
    </script>
</div>