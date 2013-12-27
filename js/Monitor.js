var Monitor = {
	init: function() {
		this.loadChart();
		jQuery('#page_loader').fadeIn();
	},
	loadChart: function() {

		$.getJSON('?updateChart', function(data) {
			jQuery('#nu').find('tbody').empty().hide();
			var dados = [],
			arrLabels = [];

			$.each(data, function(index) {

				removeM = data[index].medicao.replace('m', '');
				replacedComma = removeM.replace(',', '.');
				removeFloat = replacedComma.substr(0,4);
				dados.push(  removeFloat );
				arrLabels.push(data[index].data + ' às ' + data[index].hora.replace('</', ''));		
			});

			var lineChartData = {
					labels : arrLabels,
					datasets : [{
						fillColor : "rgba(151,187,205,0.5)",
						strokeColor : "rgba(151,187,205,1)",
						pointColor : "rgba(151,187,205,1)",
						pointStrokeColor : "#fff",
						data : dados
					},
				]
			};
			console.log(dados, arrLabels);
	
			var ctx = document.getElementById("introChart").getContext("2d");

			new Chart(ctx).Line(
				lineChartData,
					{
					scaleOverlay : false,
					scaleOverride : false,
					scaleSteps : null,
					scaleStepWidth : null,
					scaleStartValue : null,
					scaleLineColor : "rgba(0,0,0,.1)",
					scaleLineWidth : 1,
					scaleShowLabels : true,
					scaleLabel : "<%=value%>",
					scaleFontFamily : "'Arial'",
					scaleFontSize : 10,
					scaleFontStyle : "normal", 
					scaleFontColor : "#666",  
					scaleShowGridLines : true,
					scaleGridLineColor : "rgba(0,0,0,.05)",
					scaleGridLineWidth : 1, 
					bezierCurve : false,
					pointDot : true,
					pointDotRadius : 4,
					pointDotStrokeWidth : 1,
					datasetStroke : true,
					datasetStrokeWidth : 2,
					datasetFill : true,
					animation : true,
					animationSteps : 60,
					animationEasing : "easeInOutCubic",
					onAnimationComplete : null
				});
		});
		this.loadNews();
	},
	loadNews: function() {
		jQuery('.load').fadeIn();
		$.getJSON('?realTime', function(data) {
			
			jQuery('#nu').find('tbody').empty().hide();

			$.each(data, function(index) {
				jQuery('#nu').find('tbody')
				.append('<tr><td>'+data[index].medicao+'</td><td>'+data[index].data+'</td><td>'+data[index].hora.replace('</', '') +'<div class="fb-share-button" data-href="http://guih.us/" data-type="button"></div></td></tr>')
				.fadeIn(function(){
					if( jQuery('#page_loader').is(':visible') ){
						jQuery('#page_loader').fadeOut();
					}
					jQuery('.load').fadeOut();
				});

			});
			
			jQuery('#nu').paginateTable({ rowsPerPage: 5 });
			jQuery('.pager').fadeIn();

		});

		this.realTime();
	},
	realTime : function() {

		jQuery('#nu').paginateTable({ rowsPerPage: 5 });
		
		var update = 0;
		update = setTimeout(function(){Monitor.loadNews()}, 30000);
		
		jQuery('#liveupdate').change(function(){
			if(!this.checked){
				update = setTimeout(function(){Monitor.loadNews()}, 30000);
			}else{
				clearInterval(update);
			}
		});
	}
};
