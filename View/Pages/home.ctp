<script>
	window.allEvents = _.map(_.range(35), function(i) {
		return {
			 eventName: "Here is some event "+i
			,venue: "Venue "+_.random(10)
			,location: null
			,time: 1358014168714
			,image: 'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRSHHZb6Dt0Sssbz0nzT-MUvgwmtf11T2DzVkDC1ONsO2z62num'
			,price: null
			,description: "<p>Now that there is the Tec-9, a crappy spray gun from South Miami. This gun is advertised as the most popular gun in American crime. Do you believe that shit? It actually says that in the little book that comes with it: the most popular gun in American crime. Like they're actually proud of that shit.  </p>"
			,links: [
				{
				 	type: 'music'
					,link: 'https://play.google.com/music/listen?u=0'
				}
			]
		};
	});
</script>




<div id="events-list" data-role="collapsible-set">

</div>




<script id="event-template" type="text/x-handlebars-template">
	<div data-role="collapsible" class="event">
		<h3>{{eventName}}</h3>
		<div class="description">
			{{html description}}
		</div>
	</div>	
</script>


<script>
	console.log('blah');
	$(document).on('pageinit', function(){
		Handlebars.registerHelper('html', function(html) {
		  return new Handlebars.SafeString(html);
		});

		var  eventTemplate 	= Handlebars.compile($("#event-template").html())
			,$events 	= $('#events-list')
			,allEvents 	= window.allEvents 		//todo JSON get
			;

		$events
			.html(_.reduce(_.map(allEvents,eventTemplate), add2, '') )
			.trigger('create');

		function add2(x, y) { return x+y}

	});

//@sourceUrl=mainPage.js	
</script>