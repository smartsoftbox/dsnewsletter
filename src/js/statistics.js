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
            .attr("viewBox", "0 0 720 380")
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

})
