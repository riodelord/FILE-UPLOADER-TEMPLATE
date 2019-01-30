// Tooltip for the graphs
function create_tooltip() {
	$('<div id="tooltip"></div>').appendTo('body');
}

function build_graphdata(data, color) {
	var graph = [{
		data:data,
		color: color,
		points: {
			radius:4,
			fillColor: color
		}
	}];
	return graph;
}


function build_graph(container, graph_data, graph_var) {
	var plot = $.plot($(container), graph_data, {
		series: {
			points: {
				show:true,
				radius:4
			},
			lines: { show:true },
			shadowSize: 4
		},
		grid: {
			color:'#555',
			borderColor: 'transparent',
			borderWidth:20,
			hoverable: true,
			labelMargin:-5
		},
		xaxis: {
			tickColor: 'transparent',
			tickSize: 1,
			tickFormatter: function(val, axis) {
				return '<span class="normal-label">'+graph_var[1][val]+'</span><span class="responsive-label">'+graph_var[2][val]+'</span>';
			},
			labelHeight:45
		},
		yaxis: {
			tickDecimals:0
		}
	});
	return plot;
}


function attach_xaxisLabel(container, txt) {
	$('<div class="axisLabel xaxisLabel">'+txt+'</div>').appendTo($(container));
}


/****** General Hover ******/
var last = false;
var last_series = false;
function graph_bind_hover(container, txt, values) {
	$(container).bind('plothover', function(evt, position, item) {
		if(item) {
			if(last != item.dataIndex || last_series != item.seriesIndex) {
				last = item.dataIndex;
				last_series = item.seriesIndex;
				var x = item.datapoint[0]; var y = item.datapoint[1];
				var pagex = item.pageX+10; var pagey = item.pageY-30;
				var original_txt = txt;
				var windoww = $(window).width() - 150;
				if(pagex > windoww)
					pagex = windoww; 
				
				txt = txt.replace('%y%', y);
				txt = txt.replace('%values%', values[x]);
				
				console.log(txt);
				
				$('#tooltip').html(txt).css({
					top:pagey,
					left:pagex
				});
				
				txt = original_txt;
				
				if(!$('#tooltip').is('visible'))
					$('#tooltip').fadeIn(30);
			}
		}else{
			$('#tooltip').fadeOut(30);
			last = false;
			last_series = false;
		}
	});
}