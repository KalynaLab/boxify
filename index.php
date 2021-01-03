<!DOCTYPE html>

<?php
    require_once('assets/config.php');
    ini_set('display_errors', 1);error_reporting(E_ALL);
?>

<!--
    To-do:
        * Add coordinates tooltips (http://jsfiddle.net/m1erickson/yLBjM/, https://stackoverflow.com/questions/17064913/display-tooltip-in-canvas-graph, https://stackoverflow.com/questions/30795139/displaying-tooltips-on-mouse-hover-on-shapes-positioncoordinates-on-canvas?rq=1)
        * Export as (vector) image (https://codepen.io/blustemy/pen/PbRNjM?editors=1010) - 10.10.2019 the ctx.save() and ctx.restore() thing break canvas-getsvg for some reason
        * SVG stuff: Figure out why the transcript IDs are not in their proper place
        * More SVG stuff: Why isn't the thing simply replaced? - 10-10-2019 ctx.clearRect seems to be doing the thing.
        * Add download button first after the transcripts have been drawn
        * Add form (search, primers, colors, etc)
        * (Multiple) Primer search - 22-10-2019, working for up to 3 primer pairs
        * Add primer rectangles - 22-10-2019, primer bars are added. Scaled to size, which means they are tiny for ATM and other giant transcripts
        * Add primer bar "overlay" (idea from Stefan)
        * Add virtual gel picture
        * Download options (genomic sequence, transcript sequence, PCR products, etc.)
        * Create scripts for DB table generation
        * Add multiple organisms
        * Add gene search autocomplete (https://www.codexworld.com/autocomplete-textbox-using-jquery-php-mysql/)
        * Add reset settings option
        * Maybe make color palette presets
        * Add gene name search option
	      * Add breakout :D - Fix cookie
        * Add option for reversal of antisense models
        * Make sure that the sequences are always in the coding direction
        * Dark mode ^^ (https://www.developerdrive.com/css-dark-mode/)
        * "Coordinate bars"
        * Add support for split primers display
        * Add scrolling genomic sequence

    Done:
        * Catch/Handle missing CDS - 25-10-2019 That was easy, just don't bother to generate the error I added myself ><
        * Need to "hide" the genomic and spliced sequences in the HTML, because the cookie gets to big otherwise - 10-10-2019, moved away from the cookie, so the hidden seq element is no longer necessary.
        * Check cookie size for "big" genes (GRP7 - AT2G21660, ATM - AT3G48190) - 10-10-2019, moved away from cookies and store the json in the html instead.
        * Add hide CDS option - 25-10-2019, added some toggle
-->

<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>Boxify</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel="stylesheet" href="assets/css/toggle_switch.css">
        <style>
            /* Prevent text selection of draggable items */
            [draggable] {
                user-select: none;
            }
            #select-transcripts {
                width: 100%;
                margin-bottom: 1rem;
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
            #settings p { font-size: 0.9rem; }
            .settings-header { cursor: pointer; }
            .settings-content { display: none; }

            #size { width: 100%; }

            .color-form { margin-bottom: 0rem; }
            input[type="color"] {
                width: 23px;
                float: right;
            }

            #genomic-seq {
                overflow-x: hidden;
                margin-left: 125px;
                font-family: Courier New;
                font-size: 14px;
                display: none;
            }

            .caret { cursor: pointer; }
            #primer-search {
                display: none;
            }

            .PCR {
                display: none;
                font-size: 0.9em;
                margin-top: 1em;
            }

            .add-primer-button {
                cursor: pointer;
            }
            .vertical-align {
                display: flex;
                align-items: center;
            }
            .btn-circle {
                width: 30px;
                height: 30px;
                padding: 0px 0px;
                border-radius: 15px;
                text-align: center;
                font-size: 1.5em;
                font-weight: bold;
                line-height: 0;
                margin-right: 0.25em;
            }

            .given-primers {
                display: none;
                padding: 0.5em 0;
                border-top: 5px solid black;
            }
            .pcr-info { width: 100%; }
            .fragment {
                font-size: 0.9em;
                word-break: break-all;
                white-space: normal;
                font-family: Courier New;
                max-height: 5em;
                overflow-y: auto;
                display: none;
            }

            /* Scroll bar customisation */
            ::-webkit-scrollbar { width: 10px; }
            ::-webkit-scrollbar-track { border-radius: 5px; }
            ::-webkit-scrollbar-thumb {
                background: gray;
                border-radius: 5px;
            }

        </style>
    </head>
    <body>

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3 col-lg-2 p-3 bg-light" id="sticky-sidebar">
                    <div class="sticky-top">

                        <form action="" method="POST">

                            <div class="form-group">
                                <label for="gene" class="control-label">Gene</label>
                                <input type="text" class="form-control" name="gene" id="form_gene" placeholder="AT3G61860" value="AT3G61860" onkeyup="this.value = this.value.toUpperCase();">
                            </div>

                            <div id="error_messages" class="alert alert-danger"></div>

                            <button type="submit" class="btn btn-primary" id="form_submit">Start</button>

                        </form>

                        <div id="settings" class="pt-2">

                            <hr />

                            <div class="settings-header">
                                <h4 class="d-inline-block">Settings</h4> <span class="caret d-inline-block align-top">&#9660;</span>
                            </div>

                            <div class="settings-content">

                                <h5>Transcript visibility</h5>
                                <p>Toggle the transcript visibility here. Drag and drop the transcript identifiers to change their order.</p>
                                <div class="list-group text-center" id="select-transcripts">
                                </div>

                                <h5>Draw size</h5>
                                <p>Drag the slider to adjust draw size of the transcript models.</p>
                                <div class="form-group">
                                    <label for="drawSize"><span id="slideSize">800</span>px</label>
                                    <input type="range" id="size" name="drawSize"  min="600" value="800" max="1400" step="50" oninput="updateSize(value)">
                                </div>

                                <div class="form-group">
                                    <span class="align-text-bottom">Draw CDS </span>
                                    <label class="switch" id="draw-CDS" state="on">
                                        <input type="checkbox" checked>
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <h5>Colors</h5>
                                <div class="form-group color-form">
                                    <label for="transcriptColor">Transcript color: </label>
                                    <input type="color" id="transcriptColor" value="#428bca">
                                </div>

                                <div class="form-group color-form">
                                    <label for="cdsColor">CDS color: </label>
                                    <input type="color" id="cdsColor" value="#51a351">
                                </div>

                                <hr />

                            </div>


                            <p>Click the "Redraw" button to apply the changes.</p>
                            <button type="button" class="btn" id="redraw">Redraw</button>
                        </div>

                        <div id="downloads" class="pt-2">
                            <hr />
                            <a href="#" class="download-svg">Download SVG</a>
                            <!-- <a href="#" class="stretched-link"><img src="assets/img/svg_logo_240px.png" alt="Download SVG" class="download-svg"/></a> -->
                            <a href="#" class="download-png">Download PNG</a>
                        </div>
                    </div>
                </div>
                <div class="col" id="main">
                    <canvas id="boxify">
                        Your browser does not support HTML5 canvas.
                    </canvas>
                    <div id="genomic-seq"></div>

                    <div class="row">
                        <div class="col-xl-4 col-lg-6 col-sm-12 PCR">
                            <div class="form-group vertical-align add-primer-button">
                                <button type="button" class="btn btn-primary btn-circle">+</button>
                                Add primers
                            </div>

                            <div class="enter-form-here"></div>
                            <div class="given-primers" style="border-color: #d9534f;"></div>
                            <div class="pcr-results"></div>

                        </div>
                        <div class="col-xl-4 col-lg-6 col-sm-12 PCR">
                            <div class="form-group vertical-align add-primer-button">
                                <button type="button" class="btn btn-primary btn-circle">+</button>
                                Add more primers
                            </div>

                            <div class="enter-form-here"></div>
                            <div class="given-primers" style="border-color: #51a351;"></div>
                            <div class="pcr-results"></div>
                        </div>
                        <div class="col-xl-4 col-lg-6 col-sm-12 PCR">
                            <div class="form-group vertical-align add-primer-button">
                                <button type="button" class="btn btn-primary btn-circle">+</button>
                                Add more primers
                            </div>

                            <div class="enter-form-here"></div>
                            <div class="given-primers" style="border-color: #428bca;"></div>
                            <div class="pcr-results"></div>
                        </div>
                    </div>

                    <div id="svg-image"></div>
                    <div id="drawing-data"></div>

                </div>
            </div>
        </div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="assets/js/js.cookie.js"></script>
        <script src="assets/js/functions.js"></script>
        <script src="assets/js/canvas-getsvg.js"></script>
        <script type="text/javascript">

            /* Toggle switch */
            $(document).on('click', '.switch', function(e) {
                if ($(e.target).is("input")) {
                    $(this).attr("state", ($(this).attr("state") === "on" ? "off" : "on"));
                }
            });

            function reset_PCR() {
                $('.given-primers').empty().hide();
                $('.pcr-results').empty();
                $('.add-primer-button').show();
                $('.PCR').hide();
            }

            function boxify(size, data, transcripts, draw_CDS=true, exonColor="#428bca", cdsColor="#51A351") {

                $('#genomic-seq').css("width", $('#size').val()-100);

                var scale = (size-100) / (data["exonCoord"][data["exonCoord"].length - 1] - data["exonCoord"][0]),
                    first = data["exonCoord"][0],
                    scaledExonCoord = {},
                    scaledCdsCoord = {};

                // Scale the coordinates
                for (var i in data["exonCoord"]) {
                    scaledExonCoord[String(data["exonCoord"][i])] = 125 + Math.round((data["exonCoord"][i]-first) * scale);
                }
                data["scaledExonCoord"] = scaledExonCoord;

                // Scale the CDS coordinates
                for (var i in data["cdsCoord"]) {
                    scaledCdsCoord[String(data["cdsCoord"][i])] = 125 + Math.round((data["cdsCoord"][i]-first) * scale);
                }
                data["scaleFactor"] = scale;
                data["scaledCdsCoord"] = scaledCdsCoord;

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
                        canvasHeight = (nT*18)+(data["primers"].length*10)+(borderMargin*2)-6;
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
                    var canvasSVGContext = new CanvasSVG.Deferred();
                    canvasSVGContext.wrapCanvas(canvas);
                    var ctx = canvas.getContext("2d");
                    ctx.clearRect(0, 0, canvas.width, canvas.height);

                    var cT = 0;
                    for (var i = 0; i < transcripts.length; i++) {
                        var t_id = transcripts[i];
                        drawTranscript(t_id, data["transcripts"][t_id]["exons"], data["scaledExonCoord"], data["gene"]["strand"], borderMargin+(cT*18), exonColor);
                        cT += 1;
                    }

                    if (draw_CDS) {
                        cT = 0;
                        for (var i = 0; i < transcripts.length; i++) {
                            var t_id = transcripts[i];
                            drawCDS(data["transcripts"][t_id]["cds"], data["scaledCdsCoord"], borderMargin+(cT*18), cdsColor);
                            cT += 1
                        }
                    }

                    var vertical_offset = borderMargin+(cT*18);
                    var primers = data["primers"];
                    var colors = $('.given-primers').map(function() { return $(this).css("border-color");}).get();

                    for (var i = 0; i < primers.length; i++) {
                        drawPrimers(primers[i]["fwd"], primers[i]["rev"], scale, vertical_offset+(i*10), colors[i]);
                    }

                    function drawTranscript(t_id, coord, scaledCoord, strand, vOffset, color="#428BCA") {

                        ctx.font = "12px sans-serif";
                        ctx.fillStyle = "black";
                        ctx.textBaseline = "top";
                        ctx.fillText(t_id, 25, vOffset, 100);

                        ctx.fillStyle = color;
                        ctx.strokeStyle = "rgba(1, 1, 1, 0)";
                        //ctx.save();

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
                                //ctx.restore();
                            }

                            endOfLastExon = end;

                        }
                    }

                    function drawCDS(coordX, scaledCoordX, vOffsetX, color="#51A351") {

                        ctx.fillStyle = color;
                        ctx.strokeStyle = "rgba(1, 1, 1, 0)";
                        //ctx.save();

                        for (var i in coordX) {
                            var start = scaledCoordX[String(coordX[i][0])],
                                end = scaledCoordX[String(coordX[i][1])];

                            //console.log(i, start, end-start);
                            ctx.fillRect(start, vOffsetX, end-start, 12);

                        }
                    }

                    function drawPrimers(fwd, rev, scaleFactor, vOffsetX, color) {
                        ctx.fillStyle = color;
                        ctx.fillRect(125 + fwd[0]*scaleFactor, vOffsetX, (fwd[1]-fwd[0])*scaleFactor, 5)
                        ctx.fillRect(125 + rev[0]*scaleFactor, vOffsetX, (rev[1]-rev[0])*scaleFactor, 5)
                    }
                }
            }

            function primer_search(fwd, rev, transcripts, drawing_data) {

                var rx = new RegExp(fwd+".*?(?="+revcom(rev)+")"),
                    fragments = [];

                // Add the genomic DNA sequence
                trs = Array.from(transcripts);
                trs.unshift("Genomic DNA");
                drawing_data["transcripts"]["Genomic DNA"] = { "seq": drawing_data["gene"]["seq"] };

                // Look for fragments
                for (let t_id of trs) {
                    if (rx.test(drawing_data["transcripts"][t_id]["seq"])) {
                        m = drawing_data["transcripts"][t_id]["seq"].match(rx);
                        fragments[t_id] = { "product": m[0]+revcom(rev), "offset": m["index"], "len": (m[0]+revcom(rev)).length }
                    } else {
                        fragments[t_id] = { "product": "No product found", offset: -1, "len": "#NA" }
                    }
                }

                return fragments
            }

            $(document).on('click', '#form_submit', function(e) {
                e.preventDefault();

                // Reset stuff
                reset_PCR();

                var g_id = $('#form_gene').val();
                console.log(g_id);

                // Data retrieval and drawing
                $.post( "assets/ajax/get_data.php", { gene: g_id } ).done(function(output) {

                    data = $.parseJSON(output);

                    //Cookies.set('drawing-data', data);
                    //console.log(Cookies.getJSON('drawing-data'));
                    console.log(data);

                    if (data["okay"]) {

                        data["gene"]["gene_id"] = g_id;

                        // Get spliced sequences
                        var transcripts = Object.keys(data["transcripts"]),
                        	gene_start = parseInt(data["gene"]["start"]),
                        	strand = data["gene"]["strand"],
                        	seq = data["gene"]["seq"];

                        // Add the genomic sequence below the canvas
                        $('#genomic-seq').html(seq);

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

                          // Append to json drawing-data
                          if (strand == '+') {
                            data["transcripts"][t_id]["seq"] = spliced_seq;
                          } else {
                            data["transcripts"][t_id]["seq"] = spliced_seq.split('').reverse().join('');
                          }

                        }

                        // Store cookie for re-drawing
                        // Maybe implement some checks for cookie size (should not exceed 4KB)
                        //Cookies.set('drawing-data', data);

                        // Add an empty primer array for later storage
                        data["primers"] = [];

                        // Store the drawing data in a html element
                        $('#drawing-data').data(data);

                        // Draw models
                        boxify(parseInt($('#size').val()), data, transcripts, ($("#draw-CDS").attr("state") === "on" ? true : false), $('#transcriptColor').val(), $('#cdsColor').val());

                        // Add transcripts to settings
                        $('#select-transcripts').html("");
                        for (var i = 0; i < transcripts.length; i++) {
                            var t_id = transcripts[i];
                            $('#select-transcripts').append('<button type="button" class="list-group-item list-group-item-action list-group-item-dark" draggable="true">'+t_id+'</button>');
                        }

                        // Show settings
                        $('#error_messages').hide();
                        $('#settings').show();
                        $('.PCR').first().show();

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

            // Transcript size
            function updateSize(x) {
                $('#slideSize').html(x);
            }

            // Hide/Display settings
            $(document).on('click', '.settings-header', function() {
                $('.settings-content').toggle();
                $(this).find('.caret').html(($('.caret').html().charCodeAt(0) === 9660 ? "&#9650;" : "&#9660;"));
            });

            // Redraw everything
            $(document).on('click', '#redraw', function() {

                // Select the active transcripts
                var transcripts = [];
                $('#select-transcripts').find('.list-group-item-dark').each(function() {
                    transcripts.push($(this).html());
                });

                // Redraw
                //boxify(parseInt($('#size').val()), Cookies.getJSON('drawing-data'), transcripts, $('#transcriptColor').val(), $('#cdsColor').val());
                boxify(parseInt($('#size').val()), $('#drawing-data').data(), transcripts, ($("#draw-CDS").attr("state") === "on" ? true : false), $('#transcriptColor').val(), $('#cdsColor').val());
            });

            /* All the primer stuff */

            // Primer form variable
            var primer_search_form = "<form action='' method='POST'> \
                <div class='form-group'> \
                    <label for='fwd-primer' class='pcr-primer-label'>Forward primer</label> \
                    <input type='text' class='form-control' id='fwd-primer' value='GCGAATTAAGATAAAGATGAGG' onkeyup='this.value = this.value.toUpperCase();'> \
                </div> \
                <div class='form-group'> \
                    <label for='rev-primer' class='pcr-primer-label'>Reverse primer</label> \
                    <input type='text' class='form-control' id='rev-primer' value='GGAAAATTGTCGAGTTTGCG' onkeyup='this.value = this.value.toUpperCase();'> \
                </div> \
                <button type='submit' class='btn btn-primary' id='primer-search-submit'>Search</button> \
            </form>";

            $(document).on('click', '.add-primer-button', function(e) {
                $(this).siblings('.enter-form-here').html(primer_search_form);
                $(this).hide();
            });

            $(document).on('click', '#primer-search-submit', function(e) {

                // Add primers to canvas
                // Add result PCR result download

                e.preventDefault();

                var fwd = $('#fwd-primer').val(),
                    rev = $('#rev-primer').val();

                //$(this).parents(".PCR").prepend("<div class='used-primer'>Reverse: <span class='pcr-product'>"+rev+"</span></div>");
                //$(this).parents(".PCR").prepend("<div class='used-primer'>Forward: <span class='pcr-product'>"+fwd+"</span></div>");
                $(this).parents(".PCR").find(".given-primers").append("<div class='used-primer'>Forward: "+fwd+"</div>");
                $(this).parents(".PCR").find(".given-primers").append("<div class='used-primer'>Reverse: "+rev+"</div>");
                $(this).parents(".PCR").find(".given-primers").show();

                // Select the active transcripts
                var transcripts = [];
                $('#select-transcripts').find('.list-group-item-dark').each(function() {
                    transcripts.push($(this).html());
                });

                $(this).parents(".PCR").find(".pcr-results").html('');
                var drawing_data = $('#drawing-data').data();
                var fragments = primer_search($('#fwd-primer').val(), $('#rev-primer').val(), transcripts, drawing_data);

                for (t_id in fragments) {
                    $(this).parents(".PCR").find(".pcr-results").append("<div class='pcr-info'> \
                        "+t_id+" \
                        (<span class='text-muted'>"+fragments[t_id]["len"]+"</span>) \
                        <span class='caret d-inline-block align-top'>&#9660;</span> \
                        <p class='fragment'>"+fragments[t_id]["product"]+"</p> \
                    </div>");
                }

                // Store the primer location data
                // 125 + Math.round((data["exonCoord"][i]-first) * scale
                if (drawing_data["gene"]["strand"] == '+') {
                    var abs_start = fragments["Genomic DNA"]["offset"];
                    var primer_pos = {
                        "fwd": [ abs_start, abs_start+fwd.length ],
                        "rev": [ abs_start+fragments["Genomic DNA"]["len"]-rev.length, abs_start+fragments["Genomic DNA"]["len"] ]
                    }
                } else {
                    //var abs_start = drawing_data["gene"]["end"]-(fragments["Genomic DNA"]["offset"]+fragments["Genomic DNA"]["len"]);
                    var abs_start = drawing_data["gene"]["seq"].length - (fragments["Genomic DNA"]["offset"]+fragments["Genomic DNA"]["len"]);
                    var primer_pos = {
                        "fwd": [ abs_start, abs_start+rev.length ],
                        "rev": [ abs_start+(fragments["Genomic DNA"]["len"]-rev.length), abs_start+fragments["Genomic DNA"]["len"] ]
                    }
                }

                // Add the primer positions to the drawing data
                drawing_data["primers"].push(primer_pos);
                $("#drawing-data").data(drawing_data);

                // Redraw the transcripts with the primers this time
                boxify(parseInt($('#size').val()), drawing_data, transcripts, ($("#draw-CDS").attr("state") === "on" ? true : false), $('#transcriptColor').val(), $('#cdsColor').val());

                // Get rid of the form again and display the next primer search
                $(this).parents().next(".PCR").show();
                $(this).parent().parent('.enter-form-here').html('');

            });

            // Hide/Display settings
            $(document).on('click', '.pcr-info .caret', function() {
                $(this).parent().find('.fragment').toggle();
                $(this).html(($(this).html().charCodeAt(0) === 9660 ? "&#9650;" : "&#9660;"));
            });


            // Save as PNG
            document.querySelector(".download-png").addEventListener("click", (evt) => {
                var canvas = document.getElementById("boxify");
                link = evt.target;
                link.href = canvas.toDataURL();
                link.download = $('#form_gene').val()+".png"; // Change to drawing-date gene_id
            });

            // Save as PDF

            // Save as SVG
            document.querySelector(".download-svg").addEventListener("click", (evt) => {

                // Generate the SVG image
                var canvas = document.getElementById("boxify");
                var ctx = canvas.getContext("2d");
                $("#svg-image").html(ctx.getSVG());
                $("svg").attr("xmlns", "http://www.w3.org/2000/svg");

                const svgContent = document.getElementById("svg-image").innerHTML,
                      blob = new Blob([svgContent], {
                          type: "image/svg+xml"
                      }),
                      url = window.URL.createObjectURL(blob),
                      link = evt.target;//.parentElement;

                link.target = "_blank";
                link.download = $('#form_gene').val()+".svg"; // Change to drawing-data gene_id
                link.href = url;
                //console.log(link.download, url);

                // Remove it again
                $("#svg-image").html('');
            });

        </script>
    </body>
</html>
