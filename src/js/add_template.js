/**
 *  @author Marcin Kubiak
 *  @copyright  Smart Soft
 *  @license    Commercial license
 *  International Registered Trademark & Property of Smart Soft
 */

$(function () {
    //move message
    getMessageToFullScreen();

    //full screen for template form
    let dstemplate_form = document.getElementById('dstemplate_form');
    if (dstemplate_form.length) {
        dstemplate_form.classList.add("fullscreen");
    }
    // get html for iframe
    let frame_div = document.getElementsByClassName('mail-html-frame');
    if (frame_div.length) {
        let frame_html = frame_div[0].innerHTML;
        frame_div[0].innerHTML = '';

        //make html visible so it can get correct height
        document.querySelector('a[href="#view-html"]').click();
        //insert into iframe
        frame_div[0].innerHTML = '<iframe frameborder="0" scrolling="no" class="email-frame" />';
        let frame = frame_div[0].getElementsByTagName('iframe')[0];
        let doc = frame.contentWindow.document;
        doc.open();
        doc.write(frame_html);
        doc.close();
        resizeIframe(frame);
        document.querySelector('a[href="#editor"]').click(); //show first tab again
        //full width template design
        let tab_content = document.getElementById('my-nav-pills');
        //remove label
        tab_content.closest('.form-group').querySelector('label').remove();
        //remove class .col-lg-8 witch prevent full screen
        tab_content.closest('.form-group').querySelector('div').className = '';
    }
    document.getElementsByTagName('body')[0].style['opacity'] = "1"; // show body
});

/**
 * Set Tooltip use for tags
 * @param btn
 * @param message
 */
function setTooltip(btn, message) {
    $(btn).tooltip('hide')
        .attr('data-original-title', message)
        .tooltip('show');
}

/**
 * hide Tooltip use for tags
 * @param btn
 */
function hideTooltip(btn) {
    setTimeout(function () {
        $(btn).tooltip('hide');
    }, 300);
}

/*
 * hasClass check if element has class
 */
function hasClass(elem, className) {
    return elem.className.split(' ').indexOf(className) > -1;
}

/**
 * resize Iframe to full height
 * @param obj
 */
function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
}

function getMessageToFullScreen() {
    let module_confirmation = document.getElementsByClassName('module_confirmation')[0]; // confirmation
    let module_error = document.getElementsByClassName('module_error')[1]; // error message
    let form = document.getElementsByClassName('panel-heading')[0]; // title
    if (module_confirmation) {
        form.parentNode.insertBefore(module_confirmation, form.nextSibling); // insert after
    }
    if (module_error) {
        form.parentNode.insertBefore(module_error, form.nextSibling); // insert after
    }
}