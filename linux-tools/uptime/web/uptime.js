var allTimeSeries = {};
var allValueLabels = {};
var descriptions = {
    'now time': {
        'nowtime': 'server now time',
    },
    'Line time': {
        'days': 'How long the server starts days',
        'time': 'How long',
    },
    'users': {
        'users': 'Now the number of users logged into the server',
    },
    'load average': {
        'one': '1 minutes server load average',
        'five': '5 minutes server load average',
        'fifteen': '15 minutes server load average',
    }
                 
}

function streamStats() {

    var ws = new ReconnectingWebSocket("ws://192.168.1.10:8880");
    var lineCount;
    var colHeadings;

    ws.onopen = function() {
        console.log('connect');
        lineCount = 0;
    };

    ws.onclose = function() {
        console.log('disconnect');
    };

    ws.onmessage = function(e) {

        switch (lineCount++) {
            case 0: // ignore first line
                break;

            case 1: // column headings
                colHeadings = e.data.trim().split(/,/);
                break;

            default: // subsequent lines
                var colValues = e.data.trim().split(/,/);
                var stats = {};
                var len = colHeadings.length;
                for (var i = 0; i < len; i++) {
                    stats[colHeadings[i]] = colValues[i];
                }
            receiveStats(stats);
        }
    };
}

function initCharts() {
    Object.each(descriptions, function(sectionName, values) {
    	if(sectionName=='load average') {
	        var section = $('.chart.template').clone().removeClass('template').appendTo('#charts');
	        section.find('.title').text(sectionName);
	        var smoothie = new SmoothieChart({
	            grid: {
	                sharpLines: true,
	                verticalSections: 5,
	                strokeStyle: 'rgba(119,119,119,0.45)',
	                millisPerLine: 1000
	            },
	            minValue: 0,
	            labels: {
	                disabled: true
	            }
	        });
	        
	        smoothie.streamTo(section.find('canvas').get(0), 1000);
	        
	        var colors = chroma.brewer['Pastel2'];
	        var index = 0;
	        Object.each(values, function(name, valueDescription) {
	            var color = colors[index++];
	
	            var timeSeries = new TimeSeries();
	            smoothie.addTimeSeries(timeSeries, {
	                strokeStyle: color,
	                fillStyle: chroma(color).darken().alpha(0.5).css(),
	                lineWidth: 3
	            });
	            allTimeSeries[name] = timeSeries;
	
	            var statLine = section.find('.stat.template').clone().removeClass('template').appendTo(section.find('.stats'));
	            statLine.attr('title', valueDescription).css('color', color);
	            statLine.find('.stat-name').text(name);
	            allValueLabels[name] = statLine.find('.stat-value');
	        });
    	}    		
    	
    });
}

function receiveStats(stats) {
    Object.each(stats, function(name, value) {
    	if(name=='nowtime') {
    		$(".nowtime").find(".time").html(value);
    	}else if(name=='days') {
    		$(".online-time").find(".days").html(value);
    	}else if(name=='time') {
    		$(".online-time").find(".time").html(value);
    	}else{	
	        var timeSeries = allTimeSeries[name];
	        if (timeSeries) {
	            timeSeries.append(Date.now(), value);
	            allValueLabels[name].text(value);
	        }
    	}   
    });
}

$(function() {
    initCharts();
    streamStats();
});
