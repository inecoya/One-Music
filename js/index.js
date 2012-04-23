$(function () {
	var lat = 35.005104;
	var lon = 135.734389;
	var player;
	var myScroll;
	
	loaded = function() {
		myScroll = new iScroll('wrapper');
	}
	
	play = function() {
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function(position) {
					music(position.coords.latitude, position.coords.longitude);
				}, function() {
					toast("no localization");
					music(lat, lon);
				}
			);
		} else if (google.gears) {
			var geo = google.gears.factory.create('beta.geolocation');
			geo.getCurrentPosition(function(position) {
					music(position.coords.latitude, position.coords.longitude);
				}, function() {
					toast("no localization");
					music(lat, lon);
				}
			);
		} else {
			toast("no localization");
			music(lat, lon);
		}
	}
	
	pause = function() {
		$("#player")[0].pause();
		$("#play").hide();
		$("#pause").show();
	}
	
	music = function(lat, lon){
		var date = new Date();
		var h = date.getHours();
		
		var data = {lat:lat, lon:lon, h:h};
		$.ajax({
			type: "POST",
			url: "src/get_data.php",
			data: data,
			success: function(json) {
				if (json != 'error') {
					var data = eval("("+json+")");
					
					$("#player").attr("src", data["mp3"]);
					$("#player")[0].play();
					$("#title").empty();
					$("#title").html(data["title"]);
					
					$("#pause").hide();
					$("#play").show();
				} else {
					toast("ERROR");
				}
			}
		});
	}
	
	scroll = function() {
		if( (navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i)) || (navigator.userAgent.match(/iPad/i)) ) {
			window.setTimeout('window.scrollTo(0, 1)', 500);
		} else if (navigator.userAgent.match(/Android/i)) {
			window.scrollTo(0,1);
		}
	}
	
	toast = function(sMessage) {
		var container = $(document.createElement("div"));
		var message = $(document.createElement("div"));
		container.addClass("toast");
		message.addClass("message");
		message.text(sMessage);
		message.appendTo(container);
		container.appendTo($('#wrapper'));
		container.delay(100).fadeIn("slow", function() {
			$(this).delay(2000).fadeOut("slow", function() {
				$(this).remove();
			});
		});
	}
	
	$(document).bind('touchmove', function(e){
		e.preventDefault();
	});
	
	setTimeout(loaded, 200);
});