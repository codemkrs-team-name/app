<script>
	window.allEvents = _.map(_.range(35), function(i) {
		return {
			 eventName: "Here is some event "+i
			,venue: "Venue "+_.random(10)
			,location: null
			,time: new Date(1358014168714).add({hours: _.random(72)}).getTime()
			,image: 'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcRSHHZb6Dt0Sssbz0nzT-MUvgwmtf11T2DzVkDC1ONsO2z62num'
			,price: null
			,description: "<p>Now that there is the Tec-9, a crappy spray gun from South Miami. This gun is advertised as the most popular gun in American crime. Do you believe that shit? It actually says that in the little book that comes with it: the most popular gun in American crime. Like they're actually proud of that shit.  </p>"
			,source: '<a href="http://cheezburger.com/6924526080">This guy\'s blog</a>'
			,links: [
				{
				 	 type: ['music', 'gcal', 'info'][_.random(2)]
				 	,text: "Text "+i
					,link: 'https://play.google.com/music/listen?u=1'
				}
			]
		};
	});
</script>


<style>
	.event-image {
		max-width: 55px;
		max-height: 55px;
	}
	.ui-collapsible-heading-toggle .image {
		max-width: 11%;
		display: inline-block;
	}
	.ui-collapsible-heading-toggle .details {
		width: 89%;
		display: inline-block;
	}	
	.left {
		float: left;
	}
	.details {
		padding-left: .2em;
	}
	.event-name {
		width: 100%;
		display: block;
		line-height: 1em;
		font-weight: bold;
		font-size: 1.5em;
	}
	.source {
		width: 100%;
		display: block;
		line-height: 1em;		
	}
	.venue:after {
		content: ", ";
	}
	.icon {
		margin-right: 1em;
	}
	.icon.info:after{
		content: "\2139";
	}
	.icon.music:after{
		content: "\266A";
	}
	.icon.gcal:after{
		content: "\0BF0";
	}
</style>

<div id="events-list" data-role="collapsible-set">

</div>




<script id="event-template" type="text/x-handlebars-template">
	<div data-role="collapsible" class="event">
		<h3 class="ui-helper-clearfix">
			<span class="left image">{{eventImage}}</span>
			<span class="left details">
				<span class="event-name">{{eventName}}</span>
				<span class="venue">{{venue}}</span>
				<span class="time">{{time time}}</span>
				<span class="source">{{html source}}</span>
			</span>
		</h3>
		<div class="description">
			{{html description}}
			<ul class="event-links">
				{{#each links}}
				<li>{{eventLink this}}</li>
				{{/each}}
			</ul>
		</div>
	</div>	
</script>


<script>
	$(document).on('pageinit', function(){
		Handlebars.registerHelper('html', function(html) {
		  return new Handlebars.SafeString(html);
		});
		Handlebars.registerHelper('time', truthyOr('', function(timestamp) {
		  return !timestamp ? '' : new Date(timestamp).toFormat('H:MM PP');
		}));
		Handlebars.registerHelper('eventImage', truthyOr('', function() {
		  return new Handlebars.SafeString('<img src="'+this.image+'" alt="'+this.eventName+'" class="event-image"/>');
		}));
		Handlebars.registerHelper('eventLink', truthyOr('', function(link) {
		  return new Handlebars.SafeString('<a href="'+link.link+'"><span class="icon '+link.type+'"></span><span class="link-name">'+link.text+'</span></a>');
		}));

		var  eventTemplate 	= Handlebars.compile($("#event-template").html())
			,$events 	= $('#events-list')
			,allEvents 	= window.allEvents 		//todo JSON get
			;
		$events
			.html(_.reduce(_.map(allEvents,eventTemplate), add2, '') )
			.trigger('create');


		function add2(x, y) { return x+y }
		function truthyOr(def, fn) { return function(x){return x ? fn.apply(this, arguments) : def }}
	});

</script>