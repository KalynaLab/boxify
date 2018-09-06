<!DOCTYPE html>

<?php
    require_once('assets/config.php');
    ini_set('display_errors', 1);error_reporting(E_ALL);
?>

<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>Boxify</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    </head>
    <body>

        <canvas id="boxify">
            Your browser does not support HTML5 canvas.
        </canvas>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

        <script type="text/javascript">
            $.post( "assets/ajax/get_data.php", { gene: "AT1G01050" } ).done(function(output) {
                data = $.parseJSON(output);
                console.log(data);

                if (data["okay"]) {
                    var size = 600,
                        scale = (size-100) / (data["coordinates"][data["coordinates"].length - 1] - data["coordinates"][0]),
                        first = data["coordinates"][0],
                        nT = Object.keys(data["transcripts"]).length, 
                        scaledCoord = {};
                    
                    // Scale the coordinates
                    for (var i in data["coordinates"]) {
                        scaledCoord[String(data["coordinates"][i])] = 100 + Math.round((data["coordinates"][i]-first) * scale);
                    }
                    console.log(scaledCoord);


                    window.addEventListener("load", eventWindowLoaded, false);
                    
                    var Debugger = function () { };
                    Debugger.log = function (message) {
                        try {
                            console.log(message);
                        } catch (exception) {
                            return;
                        }
                    }

                    function eventWindowLoaded () {
                        canvasApp();
                    }

                    function canvasSupport() {
                        return !!document.createElement('canvas').getContext;
                    }

                    function canvasApp() {
                        
                        // Test if HTML5 Canvas is supported
                        if (!canvasSupport()) {
                            return;
                        }

                        var canvas = document.getElementById("boxify");
                        canvas.width = size;
                        canvas.height = nT*50;
                        //var canvasSVGContext = new CanvasSVG.Deferred();
                        //canvasSVGContext.wrapCanvas(canvas);
                        var ctx = canvas.getContext("2d");

                        var cT = 0;
                        for (var t_id in data["transcripts"]) {
                            drawTranscript(t_id, data["transcripts"][t_id], scaledCoord, data["gene"]["strand"], cT*18);
                            cT += 1;
                        }

                        function drawTranscript(t_id, coord, scaledCoord, strand, vOffset=0, color="#428bca") {

                            ctx.font = "12px sans-serif";
                            ctx.fillStyle = "black";
                            ctx.textBaseline = "top";
                            ctx.fillText(t_id, 0, vOffset, 100);

                            ctx.fillStyle = color;
                            ctx.strokeStyle = "rgba(1, 1, 1, 0)";
                            ctx.save();

                            var endOfLastExon = 0;
                            for (var i in coord) { 
                                var start = scaledCoord[String(coord[i][0])],
                                    end = scaledCoord[String(coord[i][1])];

                                // Draw exons
                                if (strand == '-' && i == 0) { // Negative strand triangle
                                    start += 6;
                                    ctx.beginPath();
                                    ctx.moveTo(start, vOffset);
                                    ctx.lineTo(start-6, vOffset+6);
                                    ctx.lineTo(start, vOffset+12);
                                    ctx.fill();
                                } else if (strand == '+' && i == coord.length-1) { // Positive strand triangle
                                    end -= 6;
                                    ctx.beginPath();
                                    ctx.moveTo(end, vOffset);
                                    ctx.lineTo(end+6, vOffset+6);
                                    ctx.lineTo(end, vOffset+12);
                                    ctx.fill();
                                }
                                ctx.fillRect(start, vOffset, end-start, 12);

                                // Draw introns
                                if (i > 0 && i < coord.length) {
                                    ctx.beginPath();
                                    ctx.strokeStyle = color;
                                    ctx.lineWidth = 2;
                                    ctx.moveTo(endOfLastExon, vOffset+6);
                                    ctx.quadraticCurveTo(endOfLastExon, vOffset+6, start, vOffset+6);
                                    ctx.stroke();
                                    ctx.closePath();
                                    ctx.restore();
                                }

                                endOfLastExon = end;

                            }
                        }

                    }
                }
            });
        </script>
    </body>
</html>