/**
 * 2022 Smart Soft.
 *
 *  @author    Marcin Kubiak
 *  @copyright Smart Soft
 *  @license   Commercial License
 *  International Registered Trademark & Property of Smart Soft
 */

function setTooltip(e,t){$(e).tooltip("hide").attr("data-original-title",t).tooltip("show")}function hideTooltip(e){setTimeout(function(){$(e).tooltip("hide")},300)}function hasClass(e,t){return e.className.split(" ").indexOf(t)>-1}function resizeIframe(e){e.style.height=e.contentWindow.document.documentElement.scrollHeight+"px"}function getMessageToFullScreen(){let e=document.getElementsByClassName("module_confirmation")[0],t=document.getElementsByClassName("module_error")[1],n=document.getElementsByClassName("panel-heading")[0];e&&n.parentNode.insertBefore(e,n.nextSibling),t&&n.parentNode.insertBefore(t,n.nextSibling)}$(function(){getMessageToFullScreen();let e=document.getElementById("dstemplate_form");e.length&&e.classList.add("fullscreen");let t=document.getElementsByClassName("mail-html-frame");if(t.length){let e=t[0].innerHTML;t[0].innerHTML="",document.querySelector('a[href="#view-html"]').click(),t[0].innerHTML='<iframe frameborder="0" scrolling="no" class="email-frame" />';let n=t[0].getElementsByTagName("iframe")[0],l=n.contentWindow.document;l.open(),l.write(e),l.close(),resizeIframe(n),document.querySelector('a[href="#editor"]').click();let o=document.getElementById("my-nav-pills");o.closest(".form-group").querySelector("label").remove(),o.closest(".form-group").querySelector("div").className=""}document.getElementsByTagName("body")[0].style.opacity="1"});

