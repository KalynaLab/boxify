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
        <link rel="stylesheet" href="assets/css/bootstrap-icons-1.2.2/font/bootstrap-icons.css">
        <link rel="stylesheet" href="assets/css/toggle_switch.css">
        <link rel="stylesheet" href="assets/css/style.css">
        <!-- <link rel="stylesheet" href="assets/css/dark.css"> -->
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
                            <div id="suggestion-box"></div>

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

                                <div class="form-group toggle">
                                    <span>Draw CDS </span>
                                    <label class="switch" id="draw-CDS" state="on">
                                        <input type="checkbox" checked>
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <div class="form-group toggle">
                                    <span>Display Sequence</span>
                                    <label class="switch" id="show-sequence" state="on">
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

                                <p>Click the "Redraw" button to apply the changes.</p>
                                <button type="button" class="btn" id="redraw">Redraw <i class="bi-arrow-repeat"></i></button>

                            </div>
                        </div>

                        <div id="downloads" class="pt-2">
                            <hr />
                            <a href="#" id="download-svg"><i class="bi-file-arrow-down"></i>SVG</a>
                            <a href="#" id="download-png"><i class="bi-file-arrow-down"></i>PNG</a>
                            <a href="#" id="download-pcr"><i class="bi-file-arrow-down"></i>PCR result</a>
                        </div>
                    </div>
                </div>
                <div class="col" id="main">
                    <canvas id="boxify">
                        Your browser does not support HTML5 canvas.
                    </canvas>
                    <div id="genomic-seq"></div>
                    <div id="ruler" class="A">X</div>

                    <div class="row">
                        <div class="col-xl-4 col-lg-6 col-sm-12 PCR">
                            <div class="form-group add-primer-button">
                                <i class="bi-plus-circle-fill"></i> Add primers
                            </div>

                            <div class="enter-form-here"></div>
                            <div class="given-primers" style="border-color: #d9534f;"></div>
                            <div class="pcr-results"></div>

                        </div>
                        <div class="col-xl-4 col-lg-6 col-sm-12 PCR">
                            <div class="form-group add-primer-button">
                                <i class="bi-plus-circle-fill"></i> Add more primers
                            </div>

                            <div class="enter-form-here"></div>
                            <div class="given-primers" style="border-color: #51a351;"></div>
                            <div class="pcr-results"></div>
                        </div>
                        <div class="col-xl-4 col-lg-6 col-sm-12 PCR">
                            <div class="form-group add-primer-button">
                                <i class="bi-plus-circle-fill"></i> Add more primers
                            </div>

                            <div class="enter-form-here"></div>
                            <div class="given-primers" style="border-color: #428bca;"></div>
                            <div class="pcr-results"></div>
                        </div>
                    </div>

                    <div id="svg-image"></div>

                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="assets/js/functions.js"></script>
        <script src="assets/js/canvas-getsvg.js"></script>
        <script src="assets/js/boxify.js"></script>
    </body>
</html>
