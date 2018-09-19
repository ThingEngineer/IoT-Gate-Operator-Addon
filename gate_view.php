<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Josh Campbell">
    <link rel="shortcut icon" href="/bootstrap/assets/ico/favicon.png">

    <title>IoT Gate Operator Addon</title>

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

	<style>
	.button {
	    position: relative;
	    border: none;
	    color: #FFFFFF;
	    text-align: center;
	    -webkit-transition-duration: 0.4s; /* Safari */
	    transition-duration: 0.4s;
	    text-decoration: none;
	    overflow: hidden;
	    cursor: pointer;
	}

	.button:after {
	    content: "";
	    background: #90EE90;
	    display: block;
	    position: absolute;
	    padding-top: 300%;
	    padding-left: 350%;
	    margin-left: -40px!important;
	    margin-top: -120%;
	    opacity: 0;
	    transition: all 0.8s
	}

	.button:active:after {
	    padding: 0;
	    margin: 0;
	    opacity: 1;
	    transition: 0s
	}
	</style>
	
  </head>

  <body>

    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <!--<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">-->
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">IoT Gate Operator Addon</a>
        </div>
        <div class="collapse navbar-collapse">
<!--
          <ul class="nav navbar-nav">
            <li class="active"><a href="http://ajillion.com/projects">Home</a></li>
			<li class="active"><a href="http://ajillion.com/projects">Admin</a></li>
          </ul>-->

        </div>
      </div>
    </div>

    <div class="container">

      <div class="text-center" style="padding-top:5px; padding-left:15px; padding-right:15px; ">
		<button type="button" class="button btn btn-lg btn-success btn-block bigger-button" id="open_gate">Open Gate</button><br>
		<button type="button" class="button btn btn-lg btn-primary btn-block" id="latch30m_gate">Latch Open for 30 min</button><br>
		<button type="button" class="button btn btn-lg btn-primary btn-block" id="latch8h_gate">Latch Open for 8 hours</button><br>
		<button type="button" class="button btn btn-lg btn-warning btn-block" id="close_gate">Close Now</button><br>
		<div class="jumbotron alert alert-info" role="alert">
		  <strong>The gate is:</strong> <span id="status"><?=$data['gatestate']?></span> <img class="m-0 p-0" id="status-img" src="/images/dual-ring-loader.gif" alt="..." height="32" width="32"><br>
		  Last opened <span id="last_opened"><?=$data['last_opened']?></span><br>
		  Controller Status: <span id="device_status" style="color:green"><?=$data['device_status']?></span>
		</div>
      </div>

    </div><!-- /.container -->

	<script src="/bootstrap/slider/js/modernizr.js" type="text/javascript"></script>
    <script src="/bootstrap/assets/js/jquery.js" type="text/javascript"></script>
    <script src="/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>

	<script>

	$(document).ready(function(){
		$("#status-img").hide();
		resetStatus();
	})
	
	function sendJSON(JSONout){
		var url = 'https://agent.electricimp.com/-xWQ-bU_8bJ-';
		$.post(url, JSONout);
	}
	
	$("#open_gate").click(function() {
		var JSONout = '{"c":"btn","val":{"cmd":"open"}}';
		sendJSON(JSONout);
		//$("#status").text("Opening");
		//$("#status-img").show();
		resetStatus();
	});
	
	$("#latch30m_gate").click(function() {
		var JSONout = '{"c":"btn","val":{"cmd":"latch30m"}}';
		sendJSON(JSONout);
		//$("#status").text("Opening");
		//$("#status-img").show();
		resetStatus();
	});
	
	$("#latch8h_gate").click(function() {
		var JSONout = '{"c":"btn","val":{"cmd":"latch8h"}}';
		sendJSON(JSONout);
		//$("#status").text("Opening");
		//$("#status-img").show();
		resetStatus();
	});
	
	$("#close_gate").click(function() {
		var JSONout = '{"c":"btn","val":{"cmd":"close"}}';
		sendJSON(JSONout);
		//$("#status").text("Closing");
		//$("#status-img").show();
		resetStatus();
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
						$("#status-img").hide();
				        break;
				    case 1:
				        $("#status").text('Opening');
						$("#status-img").show();
				        break;
					case 2:
				        $("#status").text('Open');
						$("#status-img").hide();
				        break;
					case 3:
				        $("#status").text('Closing');
						$("#status-img").show();
				        break;
				    default:
				        $("#status").text('Error');
						$("#status-img").hide();
				}
				
				$("#last_opened").text(data.last_opened);
				
				var device_status = (data.device_state.devicestate ? "Online" : "Offline");
				if (data.device_state.devicestate == 0) device_status = device_status + data.device_offline_timestamp;
				var device_status_color = (data.device_state.devicestate ? "green" : "red");
				$("#device_status").text(device_status);
				$('#device_status').attr('style','color:' + device_status_color);
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
