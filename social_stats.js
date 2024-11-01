jQuery(document).ready(function() {
    jQuery('.update_social').show();
    jQuery('.submit_social').click(function() {
        jQuery('.update_social').hide();
        jQuery('.loader').show();
		jQuery(this).hide();
		jQuery(".update_social_first").html('<div class="loader" style="display: block"></div>');
        jQuery.post( "admin.php?page=social_stats_update", { update: "update"}, function( data ) {
        jQuery(".update_social_first").html("Data updated");
		jQuery(".socStatsUpdate").html("Data updated");
		});
		
        return false;
    });
    
    
    //tabs
    jQuery("#tabsSocial li:first").attr("id","current");
    jQuery('#tabsSocial a').click(function(e) {
        e.preventDefault();        
        jQuery("#contentSocial > div").hide(); //Скрыть все сожержание
        jQuery("#tabsSocial li").attr("id",""); //Сброс ID
        jQuery(this).parent().attr("id","current"); // Активируем закладку
        jQuery('#' + jQuery(this).attr('title')).fadeIn(); // Выводим содержание текущей закладки
    });

});


function graphHome(dataTwitter, dataFacebook, dataGoogle, id) {
    jQuery(document).ready(function() {
        jQuery.plot(jQuery("#" + id), [{data: dataTwitter, label: "Twitter", color: '#32ccfe', }, {data: dataFacebook, label: "Facebook", color: '#3b5a9b', }, {data: dataGoogle, label: "Google+", color: '#d95232', }],
                {
                    series: {
                        pie: {
                            show: true,
                            radius: 1,
                            label: {
                                show: true,
                                radius: 3 / 4,
                                formatter: function(label, series) {
                                    return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">' + label + '<br/>' + Math.round(series.percent) + '%</div>';
                                },
                                background: {opacity: 0.5}
                            }
                        }
                    },
                    legend: {
                        show: false
                    }
                });
    });
}


function socGraph(data, id, label, color, i) {

    jQuery(function() {

        var d1 = data;
        var minimum = new Date();
        minimum.setDate(minimum.getDate() - 30);
        console.log(minimum);
        var plot = jQuery.plot(id, [
            {data: d1, label: label, color: '#' + color},
        ], {
            xaxis: { 
				    mode: "time",
					minTickSize: [1, "day"],
					//min: minimum,
					//max: (new Date()).getTime(),
					},
            series: {
                lines: {
                    show: true,
                    fill: true
                },
                points: {
                    show: true
                }
            },
            grid: {
                hoverable: true,
                clickable: true
            },
        });

        jQuery("<div id='tooltip" + i + "'></div>").css({
            position: "absolute",
            display: "none",
            border: "1px solid #fdd",
            padding: "2px",
            top: "10px",
            left: "26px",
            color: "#ffffff",
            "background-color": "#" + color,
            opacity: 0.80
        }).appendTo(id);

        jQuery(id).bind("plothover", function(event, pos, item) {
            if (item) {
                var x = item.datapoint[0].toFixed(0),
                        y = item.datapoint[1].toFixed(0);

                jQuery("#tooltip" + i).html("Count like of " + label + " " + y)
                        .fadeIn(200);
            } else {
                jQuery("#tooltip" + i).hide();
            }
        });
    });
}