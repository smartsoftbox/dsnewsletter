/**
 *  @author Marcin Kubiak
 *  @copyright  Smart Soft
 *  @license    Commercial license
 *  International Registered Trademark & Property of Smart Soft
 */

$(function () {
    $('.datetimepicker').datetimepicker({
        prevText: '',
        nextText: '',
        dateFormat: 'yy-mm-dd',
        // Define a custom regional settings in order to use PrestaShop translation tools
        currentText: 'Now',
        closeText: 'Done',
        ampm: false,
        amNames: ['AM', 'A'],
        pmNames: ['PM', 'P'],
        timeFormat: 'hh:mm:ss tt',
        timeSuffix: '',
        timeOnlyTitle: 'Choose Time',
        timeText: 'Time',
        hourText: 'Hour',
        minuteText: 'Minute',
    });

    let current_value = $('#target_customer').val();
    if (current_value) {
        showSubOption($('#groups'), 7, current_value);
        showSubOption($('#ab_day'), 5, current_value);
        showSubOption($('#newsletter_autocomplete_input'), 'cart', $('#target_news').val());
        $('#dstemplate_form #description').closest('.form-group').hide();
        make_chosen_autocomplete('newsletter_autocomplete_input', urlJson + '&action=getNews');
        make_chosen_autocomplete('product_autocomplete_input', urlJson + '&action=getProduct', true);
    }

    function showSubOption(option, id, value) {
        if (parseInt(value) === id) {
            option.closest('.form-group').slideDown();
        } else {
            option.closest('.form-group').slideUp();
        }
    }

    //show fields depend on selection
    $('#select_newsletter').on('change', function (e) {
        navigateToUrl(this.value);
    });

    //show fields depend on selection
    let target_customer = $('#target_customer');
    target_customer.on('change', function (e) {
        var value = $(this).val();
        showSubOption($('#groups'), 7, value);
        showSubOption($('#ab_day'), 5, value);
    });
    //show selected target_news newsletter input when target has changed
    let target_news = $('#target_news');
    target_news.on('change', function () {
        showSubOption($('#newsletter_autocomplete_input'), 4, this.value);
    });
    //show selected newsletter field
    if (target_news.length) {
        target_news.change();
        target_customer.change();
    }

    $('.statistics td').removeAttr('onclick');

    //fade in cron options if cron switch is disable
    let cron = $('input[name="cron"]');
    if(cron.length) {
        cron.change(function () {
            showHideCronSettings($(this).val());
        });
        showHideCronSettings($('#cron_on').is(':checked'));
    }
});

function showHideCronSettings($value) {
    if ($value == true) {
        document.getElementById('cron_hour').closest('.form-group').classList.remove("reduced-opacity");
        document.getElementById('cron_day').closest('.form-group').classList.remove("reduced-opacity");
        document.getElementById('cron_week').closest('.form-group').classList.remove("reduced-opacity");
        document.getElementById('cron_month').closest('.form-group').classList.remove("reduced-opacity");
    } else {
        document.getElementById('cron_hour').closest('.form-group').classList.add("reduced-opacity");
        document.getElementById('cron_day').closest('.form-group').classList.add("reduced-opacity");
        document.getElementById('cron_week').closest('.form-group').classList.add("reduced-opacity");
        document.getElementById('cron_month').closest('.form-group').classList.add("reduced-opacity");
    }
}

function make_chosen_autocomplete(id, url, show_id = false) {
    $('div#' + id + '_chosen .search-field input').keyup(delay(function () {
        $.ajax({
            url: url + '&q=' + $(this).val(),
            dataType: "json",
            success: function (response) {
                var exists;
                if (response) {
                    $('#' + id + '_chosen .no-results').html('No results match "' + $('#' + id + '_chosen .chosen-choices input').val() + '"');
                }
                $.each(response, function (index, item) {
                    $('#' + id + ' option').each(function () {
                        if (this.value == item.value) {
                            exists = true;
                        }
                    });
                    if (!exists) {
                        if (show_id) {
                            $('#' + id).append("<option value=" + item.value + ">" + item.caption + " id:" + item.value + "</option>");
                        } else {
                            $('#' + id).append("<option value=" + item.value + ">" + item.caption + "</option>");
                        }
                        var ChosenInputValue = $('#' + id + ' .chosen-choices input').val();
                        $("#" + id).trigger("chosen:updated");
                        $('#' + id + ' .chosen-choices input').val(ChosenInputValue);
                    }
                });
            }
        });
    }, 1000, id));
}

function navigateToUrl($parameter) {
    document.location = document.location.href + '&' + $parameter;
}

function delay(callback, ms, id) {
    var timer = 0;
    return function () {
        $('#' + id + '_chosen .no-results').html('Getting Data "' + $('#' + id + '_chosen .chosen-choices input').val() + '"');
        var context = this, args = arguments;
        clearTimeout(timer);
        timer = setTimeout(function () {
            callback.apply(context, args);
        }, ms || 0);
    };
}

function pie_chart_news(widget_name, chart_details) {
    nv.addGraph(function() {
        var chart = nv.models.pieChart()
            .x(function(d) { return d.key })
            .y(function(d) { return d.y })
            .color(d3.scale.category10().range())
            .donut(true)
            .showLabels(false)
            .showLegend(false);

        d3.select("#dash_traffic_chart2 svg")
            .datum(chart_details.data)
            .transition().duration(1200)
            .call(chart);

        nv.utils.windowResize(chart.update);

        return chart;
    });
}
