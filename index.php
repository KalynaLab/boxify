<!DOCTYPE html>

<?php
    require_once('assets/config.php');
    ini_set('display_errors', 1);error_reporting(E_ALL);
?>

<!--
    To-do:
        * Catch/Handle missing CDS
        * Change transcript/CDS colors
        * Add coordinates tooltips (http://jsfiddle.net/m1erickson/yLBjM/, https://stackoverflow.com/questions/17064913/display-tooltip-in-canvas-graph, https://stackoverflow.com/questions/30795139/displaying-tooltips-on-mouse-hover-on-shapes-positioncoordinates-on-canvas?rq=1) 
        * Export as (vector) image
        * Add form (search, primers, colors, etc)
        * (Multiple) Primer search
        * Download options (genomic sequence, transcript sequence, PCR products, etc.)
        * Create scripts for DB table generation
        * Add multiple organisms
        * Add gene search autocomplete (https://www.codexworld.com/autocomplete-textbox-using-jquery-php-mysql/)
        * Need to "hide" the genomic and spliced sequences in the HTML, because the cookie gets to big otherwise
-->

<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>Boxify</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <style>
            /* Prevent text selection of draggable items */
            [draggable] { 
                user-select: none;
            }
            #select-transcripts {
                width: 100%;
            }
            #select-transcripts .list-group-item {
                padding: .25rem .75rem;
                font-family: sans-serif;
                font-size: 12px;
            }
            #sticky-sidebar { 
                min-height: 100vh;
            }
            #main {
                overflow-x: auto;
                white-space: nowrap;
            }
            #error_messages { display: none; }
            #settings { display: none; }
            #seq {  }
        </style>
    </head>
    <body>

        <div class="container-fluid">
            <div class="row">
                <div class="col-3 p-3 bg-light" id="sticky-sidebar">
                    <div class="sticky-top">

                        <form action="" method="POST">

                            <div class="form-group">
                                <label for="gene" class="control-label">Gene</label>
                                <input type="text" class="form-control" name="gene" id="form_gene" placeholder="AT3G61860" value="AT3G61860">
                            </div>

                            <div id="error_messages" class="alert alert-danger"></div>

                            <button type="submit" class="btn btn-primary" id="form_submit">Start</button>

                        </form>

                        <div id="settings" class="pt-2">

                            <hr />

                            <div class="sidebar-header">
                                <h4>Settings</h4>
                            </div>
                            <p>Toggle the transcript visibility here. Drag and drop the transcript identifiers to change their order.</p>
                            <div class="list-group text-center" id="select-transcripts">
                            </div>

                            <div id="size">600</div>
                            <p>Click the "Redraw" button to apply the changes</p>
                            <button type="button" class="btn" id="redraw">Redraw</button>
                        </div>
                    </div>
                </div>
                <div class="col" id="main">
                    <canvas id="boxify">
                        Your browser does not support HTML5 canvas.
                    </canvas>

                    <pre id="seq"></pre>
                </div>
            </div>
        </div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="assets/js/js.cookie.js"></script>

        <script type="text/javascript">

            function boxify(size, data, transcripts, exonColor="#428bca", cdsColor="#51A351") {

                var nT = transcripts.length;
                window.addEventListener("load", eventWindowLoaded(), false);

                var Debugger = function () { };
                Debugger.log = function (message) {
                    try {
                        console.log(message);
                    } catch (exception) {
                        return;
                    }
                }

                function eventWindowLoaded () {
                    var borderMargin = 25,
                        canvasWidth = size+(borderMargin*2),
                        canvasHeight = (nT*18)+(borderMargin*2)-6;
                    canvasApp(borderMargin, canvasWidth, canvasHeight);
                }

                function canvasSupport() {
                    return !!document.createElement('canvas').getContext;
                }

                function canvasApp(borderMargin, canvasWidth, canvasHeight) {

                    // Test if HTML5 Canvas is supported
                    if (!canvasSupport()) {
                        return;
                    }

                    var canvas = document.getElementById("boxify");
                    canvas.width = canvasWidth;
                    canvas.height = canvasHeight;
                    var ctx = canvas.getContext("2d");

                    ctx.fillStyle = "white";
                    ctx.fillRect(0, 0, canvas.width, canvas.height);

                    var cT = 0;
                    for (var i = 0; i < transcripts.length; i++) {
                        var t_id = transcripts[i];
                        drawTranscript(t_id, data["transcripts"][t_id]["exons"], data["scaledExonCoord"], data["gene"]["strand"], borderMargin+(cT*18), exonColor);
                        cT += 1;
                    }

                    cT = 0;
                    for (var i = 0; i < transcripts.length; i++) {
                        var t_id = transcripts[i];
                        drawCDS(data["transcripts"][t_id]["cds"], data["scaledCdsCoord"], borderMargin+(cT*18), cdsColor);
                        cT += 1
                    }

                    function drawTranscript(t_id, coord, scaledCoord, strand, vOffset, color="#428bca") {

                        ctx.font = "12px sans-serif";
                        ctx.fillStyle = "black";
                        ctx.textBaseline = "top";
                        ctx.fillText(t_id, 25, vOffset, 100);

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

                    function drawCDS(coordX, scaledCoordX, vOffsetX, color="#51A351") {

                        ctx.fillStyle = color;
                        ctx.strokeStyle = "rgba(1, 1, 1, 0)";
                        ctx.save();

                        for (var i in coordX) { 
                            var start = scaledCoordX[String(coordX[i][0])],
                                end = scaledCoordX[String(coordX[i][1])];

                            //console.log(i, start, end-start);
                            ctx.fillRect(start, vOffsetX, end-start, 12);

                        }
                    }
                }
            }

            $(document).on('click', '#form_submit', function(e) {
                e.preventDefault();

                var g_id = $('#form_gene').val();
                console.log(g_id);

                // Data retrieval and drawing
                $.post( "assets/ajax/get_data.php", { gene: g_id } ).done(function(output) {

                    data = $.parseJSON(output);

                    //Cookies.set('drawing-data', data);
                    //console.log(Cookies.getJSON('drawing-data'));

                    //console.log(data);

                    if (data["okay"]) {
                        var size = parseInt($('#size').html()),
                            scale = (size-100) / (data["exonCoord"][data["exonCoord"].length - 1] - data["exonCoord"][0]),
                            first = data["exonCoord"][0],
                            scaledExonCoord = {},
                            scaledCdsCoord = {};
                        
                        // Scale the coordinates
                        for (var i in data["exonCoord"]) {
                            scaledExonCoord[String(data["exonCoord"][i])] = 125 + Math.round((data["exonCoord"][i]-first) * scale);
                        }
                        data["scaledExonCoord"] = scaledExonCoord;
                        //console.log(scaledExonCoord);

                        // Scale the CDS coordinates
                        for (var i in data["cdsCoord"]) {
                            scaledCdsCoord[String(data["cdsCoord"][i])] = 125 + Math.round((data["cdsCoord"][i]-first) * scale);
                        }
                        data["scaledCdsCoord"] = scaledCdsCoord;
                        //console.log(scaledCdsCoord);

                        // Get spliced sequences
                        var transcripts = Object.keys(data["transcripts"]),
                        	gene_start = parseInt(data["gene"]["start"]),
                        	strand = data["gene"]["strand"],
                        	seq = data["gene"]["seq"];

                        // Save genomic sequence in html
                        $('#seq').append('<span id="genomic">'+seq+'</span>');
                        delete data["gene"]["seq"];

                        if (strand == '-') { seq = seq.split('').reverse().join(''); }
                        for (var i = 0; i < transcripts.length; i++) {
                        	var t_id = transcripts[i],
                        		exons = data["transcripts"][t_id]["exons"],
                        		spliced_seq = "";

                        	for (var j in exons) {
                        		var start = exons[j][0],
                        			end = exons[j][1];
                        		spliced_seq += seq.slice(start-gene_start, (end-gene_start)+1);
                        	}

                        	if (strand == '+') {
                        		$('#seq').append('<span id="'+t_id+'" class="spliced_seq">'+spliced_seq+'</span><br>');
                        	} else {
                        		$('#seq').append('<span id="'+t_id+'" class="spliced_seq">'+spliced_seq.split('').reverse().join('')+'</span><br>');
                        	}

                        }

                        // Store cookie for re-drawing
                        // Maybe implement some checks for cookie size (should not exceed 4KB)
                        Cookies.set('drawing-data', data);

                        // Draw models
                        boxify(size, data, transcripts);

                        // Add transcripts to settings
                        $('#select-transcripts').html("");
                        for (var i = 0; i < transcripts.length; i++) {
                            var t_id = transcripts[i];
                            $('#select-transcripts').append('<button type="button" class="list-group-item list-group-item-action list-group-item-dark" draggable="true">'+t_id+'</button>');
                        }

                        // Show settings
                        $('#error_messages').hide();
                        $('#settings').show();

                    } else {
                        $('#error_messages').html("<strong>Error!</strong> "+data["messages"]);
                        $('#error_messages').show();
                    }
                });

            });



            // Transcript order dragging
            var dragging = null,
                border = 'dashed 2px black',
                dragParent = 'select-transcripts';

            document.addEventListener('dragstart', function(event) {
                dragging = event.target;
                event.dataTransfer.setData('text/html', dragging);
            });

            document.addEventListener('dragover', function(event) {
                event.preventDefault();
                if ( $(event.target).parent()[0]['id'] === dragParent ) {
                    var bounding = event.target.getBoundingClientRect(),
                        offset = bounding.y + (bounding.height/2);
                    if (event.clientY - offset > 0) {
                        event.target.style['border-bottom'] = border;
                        event.target.style['border-top'] = '';
                    } else {
                        event.target.style['border-bottom'] = '';
                        event.target.style['border-top'] = border;
                    }
                }
            });

            document.addEventListener('dragleave', function(event) {
                event.target.style['border-bottom'] = '';
                event.target.style['border-top'] = '';
            });

            document.addEventListener('drop', function(event) {
                event.preventDefault();

                if ( $(event.target).parent()[0]['id'] === dragParent ) {

                    if (event.target.style['border-bottom'] !== '') {
                        event.target.style['border-bottom'] = '';
                        event.target.parentNode.insertBefore(dragging, event.target.nextSibling);
                    } else {
                        event.target.style['border-top'] = '';
                        event.target.parentNode.insertBefore(dragging, event.target);
                    }
                }
            });

            $(document).on('click', '#select-transcripts button', function() {
                $(this).toggleClass('list-group-item-dark');
            });

            // Redraw everything
            $(document).on('click', '#redraw', function() {
                var transcripts = [];
                $('#select-transcripts').find('.list-group-item-dark').each(function() {
                    transcripts.push($(this).html());
                });
                boxify(parseInt($('#size').html()), Cookies.getJSON('drawing-data'), transcripts);
            });

        </script>
    </body>
</html>