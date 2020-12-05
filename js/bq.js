var current = 0;

var minVol = 0;
var minIss = 0;
var maxVol = 99;
var maxIss = 0;

$(window).ready(function(){
	minVol = ($("#minVol").text().length > 0) ? $("#minVol").text() : minVol;
	minIss = ($("#minIss").text().length > 0) ? $("#minIss").text() : minIss;
	maxVol = ($("#maxVol").text().length > 0) ? $("#maxVol").text() : maxVol;
	maxIss = ($("#maxIss").text().length > 0) ? $("#maxIss").text() : maxIss;

	var pathname = window.location.pathname;	
	if(pathname.charAt(pathname.length-1) == '/' || pathname.search(/index.php/) != -1) {
		// --- first section initially expanded:
		$("h3.decadeHeading").toggler({initShow: "div.collapse:first"});
		// --- Other options:
		//$("h3.decadeHeading").toggler({method: "toggle", speed: 0});
		//$("h3.decadeHeading").toggler({method: "toggle"});
		//$("h3.decadeHeading").toggler({speed: "fast"});
		//$("h3.decadeHeading").toggler({method: "fadeToggle"});
		//$("h3.decadeHeading").toggler({method: "slideFadeToggle"});    
		//$("#all-content").expandAll({trigger: "h2.expand", ref: "div.demo",  speed: 300, oneSwitch: false});

		$("#allIssues").css("display", "block");
	} else if (pathname.search(/[0-9]{1,2}.[1-4][-a-z1-4]{0,2}.[a-zA-Z]*/) != -1) {
			$('.image-expand').fancybox({
			});	
	} else if(pathname.search(/Emend/) != -1) {
			//Remove emendation entries for unpublished articles
            $('div#core div').each(function(index) {
				var id = $(this).attr('id');
				if(id.match(/[0-9]{1,2}.[1-4][-a-z1-4]{0,2}/)) {
					var parts = id.split('.');
					var vol = Number(parts[0]);
					var iss = Number(parts[1]);
					if((vol > minVol || (vol == minVol && iss >= minIss)) && (vol < maxVol || (vol == maxVol && iss <= maxIss))) {
						// fine
					} else {
						$(this).remove();
					}
				}
            });
	} else if (pathname.search(/search/) != -1) {
		$("h4.search-advanced-heading").toggler();
		$("h4.search-sort-heading").toggler();
	}
	
	tardis(); // we can run this on "ready" (not waiting for "load") because main css is in the HTML, not loaded
});

$(window).load(function(){
});

function tardis() {
// (time and) relative dimensions in space
	fitContent();
	$( window ).resize(function() {
		fitContent();
	});
	if(window.location.hash != '') {
		scrollToElement ($(window.location.hash), 0);
	}
	
	$('a[href^=#]').click(function(e){
		e.preventDefault();
		scrollToElement ($($.attr(this, 'href')), 500);
		window.location.hash = $.attr(this, 'href');
		return false;
	});
}

function scrollToElement ($element, ms) {
	$('#all-content').animate({
		scrollTop: $element.offset().top - $('#all-content-inner').offset().top
	}, ms);
}

function fitContent() {
	var headerOffset = $( '#header' ).height() + 1;
	$('#all-content').height($( window ).height()-headerOffset);
}

function getParams () {
  // This function is anonymous, is executed immediately and 
  // the return value is assigned to QueryString!
  var query_string = {};
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
    	// If first entry with this name
    if (typeof query_string[pair[0]] === "undefined") {
      query_string[pair[0]] = pair[1];
    	// If second entry with this name
    } else if (typeof query_string[pair[0]] === "string") {
      var arr = [ query_string[pair[0]], pair[1] ];
      query_string[pair[0]] = arr;
    	// If third or later entry with this name
    } else {
      query_string[pair[0]].push(pair[1]);
    }
  } 
    return query_string;
}
