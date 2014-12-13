/**
 * Created by morontt.
 * Date: 13.12.14
 * Time: 22:31
 */
$(function () {
    $('.comentators-item:even').css('margin-right', '0.6em');

    var chart = new dhtmlXChart({
        view:"spline",
        container:"chart_container",
        value:"#value#",
        tooltip:{
            template:"#value#",
            dx:20,
            dy:-10
        },
        item:{
            borderColor:"#335900",
            borderWidth:2,
            color:"#69ba00"
        },
        line:{
            color:"#69ba00",
            width:3
        },
        xAxis:{
            lines:true,
            template:"#label#"
        },
        yAxis:{
            template:"{obj}"
        }
    });

    chart.parse(cartData, "json");
});
