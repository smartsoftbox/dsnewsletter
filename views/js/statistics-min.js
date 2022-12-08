/**
 * 2022 Smart Soft.
 *
 *  @author    Marcin Kubiak
 *  @copyright Smart Soft
 *  @license   Commercial License
 *  International Registered Trademark & Property of Smart Soft
 */

$(function(){nv.addGraph(function(){var t=nv.models.lineChart().margin({left:100}).useInteractiveGuideline(!0).transitionDuration(350).showLegend(!0).showYAxis(!0).showXAxis(!0);t.xAxis.axisLabel("Date").tickFormat(function(t){return d3.time.format("%x")(new Date(1e3*t))}),t.yAxis.axisLabel("Amount").tickFormat(d3.format(".02f"));var e=function(){for(var t=[],e=[],s=[],a=[],n=0;n<stats.length;n++)t.push({x:stats[n].date_sent,y:stats[n].sent_number}),e.push({x:stats[n].date_sent,y:stats[n].open}),s.push({x:stats[n].date_sent,y:stats[n].click}),a.push({x:stats[n].date_sent,y:stats[n].failed});stats.length||(t.push({x:0,y:0}),e.push({x:0,y:0}),s.push({x:0,y:0}),a.push({x:0,y:0}));return[{values:t,key:"Send",color:"#ff7f0e"},{values:e,key:"Open",color:"#2ca02c"},{values:s,key:"Click",color:"#7777ff"},{values:a,key:"Failed",color:"#7777ff"}]}();return d3.select("#chart svg").attr("preserveAspectRatio","xMinYMin meet").attr("viewBox","0 0 720 380").datum(e).call(t),nv.utils.windowResize(function(){t.update()}),t})});