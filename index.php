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
        <link rel="stylesheet" href="assets/css/bootstrap-icons-1.2.2/font/bootstrap-icons.css">
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="assets/css/toggle_switch.css">
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body>

        <div class="container">
            <aside>

                <div id="boxify-header">
                    <h1>Boxify</h1>
                    <span>: transcript drawing made easy!</span>
                </div>

                <div id="search">
                    <i class="bi-search"></i>
                    <input type="search" id="search-gene" placeholder="Search gene" aria-label="Search for a gene" value="AT3G61860" autofocus>
                    <div id="suggestion-box"></div>
                    <div id="error-messages"></div>
                </div>

                <div id="transcripts">
                    <p class="info">
                        Select transcripts to display and drag to change their order.
                    </p>
                    <ul id="select-transcripts"></ul>
                </div>

                <div id="settings">

                    <p class="info">Adjust the size of the transcript models.</p>
                    <div id="adjust-size">
                        <label for="drawSize"><span id="slideSize">800</span>px</label>
                        <input type="range" id="size" name="drawSize"  min="600" value="800" max="1400" step="50">
                    </div>

                    <div class="toggle">
                        <span>Draw CDS </span>
                        <label class="switch" id="draw-CDS" state="on">
                            <input type="checkbox" checked>
                            <span class="slider round"></span>
                        </label>
                    </div>

                    <div class="toggle">
                        <span>Display Sequence</span>
                        <label class="switch" id="show-sequence" state="on">
                            <input type="checkbox" checked>
                            <span class="slider round"></span>
                        </label>
                    </div>

                    <p class="info">Adjust the transcript models fill and stroke colors</p>
                    <div class="colors">
                        <span class="w50">Transcript</span>
                        <div class="color-form">
                            <label for="transcript-fill-color">Fill</label>
                            <input type="color" id="transcript-fill-color" value="#428bca">
                        </div>

                        <div class="color-form">
                            <label for="transcript-stroke-color">Stroke</label>
                            <input type="color" id="transcript-stroke-color" value="#428bca">
                        </div>
                    </div>

                    <div class="colors">
                        <span class="w50">CDS</span>
                        <div class="color-form">
                            <label for="cds-fill-color">Fill</label>
                            <input type="color" id="cds-fill-color" value="#51a351">
                        </div>

                        <div class="color-form">
                            <label for="cds-stroke-color">Stroke</label>
                            <input type="color" id="cds-stroke-color" value="#51a351">
                        </div>
                    </div>

                    <div id="reset-settings">
                        <span>Reset settings</span>
                        <i class="bi-arrow-repeat"></i>
                    </div>

                </div>

                <div id="downloads">
                    <p class="info">Downloads</p>
                    <button id="download-png"><i class="bi-file-arrow-down"></i>PNG</button>
                    <button id="download-svg"><i class="bi-file-arrow-down"></i>SVG</button>
                    <button id="download-pcr"><i class="bi-file-arrow-down"></i>PCR result</button>
                </div>

                <div id="theme">
                    <p class="info">Toggle between a light and dark theme.</p>
                    <div id="toggle-theme-wrapper">
                        <i class="bi-sun"></i>
                        <label class="switch" id="toggle-theme" state="off">
                            <input type="checkbox">
                            <span class="slider round"></span>
                        </label>
                        <i class="bi-moon"></i>
                    </div>
                </div>

            </aside>
            
            <main>

                <div class="loader"></div>

                <canvas id="boxify">
                    Your browser does not support HTML5 support.
                </canvas>
                <i id="expand-seq" class="bi-caret-down-fill"></i>
                <div id="sequences">
                    <div id="genomic-seq"></div>
                    <div id="display-seq"></div>
                </div>

                <div id="pcr-wrapper">

                    <div id="add-primer-button">
                        <i class="bi-plus-circle-fill"></i> Add primers
                    </div>

                </div>

                <canvas id="save-image"></canvas>

            </main>

            <footer>
                <span id="copyright">&copy; 2020 - Peter Venhuizen</span>
                <span id="github">See the Boxify <a href="https://github.com/KalynaLab/boxify">GitHub</a> page for help and to report any bugs and issues via the <a href="https://github.com/KalynaLab/boxify/issues">issue tracker</a></span>
            </footer>
        </div>

        <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <script src="assets/js/functions.js"></script>
        <script src="assets/js/canvas-getsvg.js"></script>
        <script src="assets/js/boxify.js"></script>
    </body>
</html>