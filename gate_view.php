<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Josh Campbell">
    <link rel="shortcut icon" href="/bootstrap/assets/ico/favicon.png">

    <title>IoT Gate Opperator Addon</title>

    <!-- Bootstrap core CSS -->
    <link href="/bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="/bootstrap/slider/css/slider.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="/bootstrap/css/main.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="/bootstrap/assets/js/html5shiv.js"></script>
      <script src="/bootstrap/assets/js/respond.min.js"></script>
    <![endif]-->

  </head>

  <body>

    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">IoT Gate Opperator Addon</a>
        </div>
        <div class="collapse navbar-collapse">
<!--
          <ul class="nav navbar-nav">
            <li class="active"><a href="http://projects.ajillion.com/gate">Home</a></li>
			<li class="active"><a href="http://projects.ajillion.com/gate">Admin</a></li>
          </ul>-->

        </div>
      </div>
    </div>

    <div class="container">

      <div class="starter-template">
		<button type="button" class="btn btn-lg btn-success btn-block bigger-button" id="open_gate">Open Gate</button><br>
		<button type="button" class="btn btn-lg btn-primary btn-block" id="latch30m_gate">Latch Open for 30 min</button><br>
		<button type="button" class="btn btn-lg btn-primary btn-block" id="latch8h_gate">Latch Open for 8 hours</button><br>
		<button type="button" class="btn btn-lg btn-warning btn-block" id="close_gate">Close Now</button>
		<br><br>
		<div class="jumbotron alert alert-info" role="alert">
		  <strong>Gate Status: </strong><span id="status"><?=$data['gatestate']?></span><br>
		  Last opened <span id="last_opened"><?=$data['last_opened']?></span>
		</div>
      </div>

    </div><!-- /.container -->

	<script src="/bootstrap/slider/js/modernizr.js" type="text/javascript"></script>
    <script src="/bootstrap/assets/js/jquery.js" type="text/javascript"></script>
    <script src="/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>

	<script>

	$(document).ready(function(){
		resetStatus();
	})

	function sendJSON(JSONout){
		var url = 'https://agent.electricimp.com/YOUR-AGENT-CODE';
		$.post(url, JSONout);
	}

	$("#open_gate").click(function() {
		var JSONout = '{"c":"btn","val":{"cmd":"open"}}';
		sendJSON(JSONout);
		$("#status").text("Opening...");
	});

	$("#latch30m_gate").click(function() {
		var JSONout = '{"c":"btn","val":{"cmd":"latch30m"}}';
		sendJSON(JSONout);
		$("#status").text("Opening...");
	});

	$("#latch8h_gate").click(function() {
		var JSONout = '{"c":"btn","val":{"cmd":"latch8h"}}';
		sendJSON(JSONout);
		$("#status").text("Opening...");
	});

	$("#close_gate").click(function() {
		var JSONout = '{"c":"btn","val":{"cmd":"close"}}';
		sendJSON(JSONout);
		$("#status").text("Closing...");
	});

	function resetStatus() {
		// Target url
		var target = 'http://projects.ajillion.com/get_gate_state';

		// Request
		var data = {
			agent : 'app'
		};

		// Send ajax post request
		$.ajax({
			url: target,
			dataType: 'json',
			type: 'POST',
			data: data,
			success: function(data, textStatus, XMLHttpRequest)
			{
				switch(data.gatestate) {
				    case 0:
				        $("#status").text('Closed');
				        break;
				    case 1:
				        $("#status").text('Opening...');
				        break;
					case 2:
				        $("#status").text('Open');
				        break;
					case 3:
				        $("#status").text('Closing...');
				        break;
				    default:
				        $("#status").text('Error');
				}

				$("#last_opened").text(data.last_opened);
			},
			error: function(XMLHttpRequest, textStatus, errorThrown)
			{
				// Error message
				$("#status").text('Server Error');
			}
		});

		setTimeout(resetStatus, 3000);
	 }

	</script>
  </body>
</html>
