/**
 *  @author Marcin Kubiak
 *  @copyright  Smart Soft
 *  @license    Commercial license
 *  International Registered Trademark & Property of Smart Soft
 */

$(function () {
    /*These lines are all chart setup.  Pick and choose which chart features you want to utilize. */
    nv.addGraph(function() {
        var chart = nv.models.lineChart()
            .margin({left: 100})  //Adjust chart margins to give the x-axis some breathing room.
            .useInteractiveGuideline(true)  //We want nice looking tooltips and a guideline!
            .transitionDuration(350)  //how fast do you want the lines to transition?
            .showLegend(true)       //Show the legend, allowing users to turn on/off line series.
            .showYAxis(true)        //Show the y-axis
            .showXAxis(true)        //Show the x-axis
        ;

        chart.xAxis     //Chart x-axis settings
            .axisLabel('Date')
            .tickFormat(function(d) {
                return d3.time.format('%x')(new Date(d * 1000))
            });

        chart.yAxis     //Chart y-axis settings
            .axisLabel('Amount')
            .tickFormat(d3.format('.02f'));

        /* Done setting the chart up? Time to render it!*/
        var myData = loadStats();   //You need data...

        d3.select('#chart svg')    //Select the <svg> element you want to render the chart in.
            .attr("preserveAspectRatio", "xMinYMin meet")
            .attr("viewBox", "0 0 720 340")
            .datum(myData)         //Populate the <svg> element with chart data...
            .call(chart);          //Finally, render the chart!

        //Update the chart when window resizes.
        nv.utils.windowResize(function() { chart.update() });
        return chart;
    });
    /**************************************
     * Simple test data generator
     */
    function loadStats() {
        var send = [],open = [],
            click = [], failed = [];

        //Data is represented as an array of {x,y} pairs.
        for (var i = 0; i < stats.length; i++) {
            send.push( {x: stats[i]['date_sent'], y: stats[i]['sent_number']} );
            open.push( {x: stats[i]['date_sent'], y: stats[i]['open']} );
            click.push( {x: stats[i]['date_sent'], y: stats[i]['click']} );
            failed.push( {x: stats[i]['date_sent'], y: stats[i]['failed']} );
        }

        if(!stats.length) {
            send.push( {x: 0, y: 0} );
            open.push( {x: 0, y: 0} );
            click.push( {x: 0, y: 0} );
            failed.push( {x: 0, y: 0} );
        }

        //Line chart data should be sent as an array of series objects.
        return [
            {
                values: send,      //values - represents the array of {x,y} data points
                key: 'Send', //key  - the name of the series.
                color: '#ff7f0e'  //color - optional: choose your own line color.
            },
            {
                values: open,
                key: 'Open',
                color: '#2ca02c'
            },
            {
                values: click,
                key: 'Click',
                color: '#7777ff'
            },
            {
                values: failed,
                key: 'Failed',
                color: '#7777ff'
            }
        ];
    }

    $('.timepicker').datetimepicker({
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

    //sent newsletter manually
    //remove default onclick event
    $(document.body).on('click', 'a.sent', function (e) {
        e.preventDefault();
        var animateFunc = function () {
            $.ajax({
                type: 'GET',
                url: urlJson + '&ajax=1&action=getProgress',
                success: function (response) {
                    var progress = parseInt(response);
                    console.log(progress);
                    if (progress < 100 || progress === -1) {
                        if (progress === -1) {
                            response = 'estimating...'
                        }
                        $('#overlay').show();
                        $('#progress').html(response);
                        setTimeout(animateFunc, 1000);
                    } else {
                        clearInterval(animateFunc);
                        $('#overlay').hide();
                    }
                }
            });
        };
        setTimeout(animateFunc, 1000);

        $('a.sent').fancybox({
            type: 'ajax',
            onCleanup: function () {
                window.location.reload(true);
            },
            afterClose: function () {
                window.location.reload(true);
            },
        });
    });

    $('.statistics td').removeAttr('onclick');
});

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
