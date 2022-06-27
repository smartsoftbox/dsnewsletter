{*
*  @author Marcin Kubiak
*  @copyright  Smart Soft
*  @license    Commercial license
*  International Registered Trademark & Property of Smart Soft
*}

<div id="completion_mail_wrapper" class="clear panel">
    <div id="completion_newsletter">
        <div id="newsletter_progressbar_tab"></div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#newsletter_progressbar_tab').progressbar();
        //sent newsletter manually
        //remove default onclick event
        $(document.body).on('click', 'a.sent', function (e) {
            e.preventDefault();
            $('#completion_mail_wrapper').show();
            var animateFunc = function () {
                $.ajax({
                    type: 'GET',
                    url: urlJson + '&ajax=1&action=getProgress',
                    success: function (progress) {
                        if (progress < 100) {
                            $('#newsletter_progressbar_tab').progressbar("value", parseInt(progress));
                            setTimeout(animateFunc, 1000);
                        } else {
                            $('#newsletter_progressbar_tab').progressbar("value", parseInt(progress));
                            clearInterval(animateFunc);
                        }
                    }
                });
            };
            setTimeout(animateFunc, 1000);

            $('a.sent').fancybox({
                type: 'ajax',
                onCleanup: function () {
                    window.location.reload();
                },
                afterClose: function () {
                    window.location.reload();
                },
            });
        });
    });
</script>
