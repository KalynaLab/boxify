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
        <!-- <link rel="stylesheet" href="assets/css/dark.css"> -->
        <style>
            :root {
                --00dp: #121212;
                --01dp: #1e1e1e;
                --02dp: #222222;
                --03dp: #242424;
                --04dp: #272727;
                --06dp: #2c2c2c;
                --08dp: #2e2e2e;
                --12dp: #333333;
                --16dp: #353535;
                --24dp: #383838;
                --boxify-blue: #428bca;
                --boxify-green: #51a351; 
            }
            * { 
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: 'Roboto', sans-serif;
            }
            .container {
                display: grid;

                grid-template-areas:
                    "settings content"
                    "footer footer";

                grid-template-columns: 250px 1fr;
                grid-template-rows: 1fr auto;

                height: 100vh;
            }

            aside {
                grid-area: settings;
                border-right: 1px solid black;
            }

            aside h2 {
                text-align: center;
                padding: 0.25em 0;
            }

            /* SEARCH */
            #search, #transcripts, #settings, #downloads, #theme {
                border-bottom: 1px solid black;
            }
            #search {
                box-sizing: border-box;
                padding: 1em;
            }
            #search .bi-search {
                position: absolute;
                margin: 0.5em 0em 0.5em 0.75em;
            }
            input[type="search"] {
                border-radius: 20px; 
                border: 1px solid black;
                box-sizing: border-box;
                width: 100%;
                padding: 0.25em 0.75em 0.25em 1.8em;
                font-size: 21px;
                text-transform: uppercase;
            }
            input[type="search"]:focus {
                outline: none;
            }
            ::-webkit-input-placeholder { /* WebKit browsers */
                text-transform: none;
            }
            :-moz-placeholder { /* Mozilla Firefox 4 to 18 */
                text-transform: none;
            }
            ::-moz-placeholder { /* Mozilla Firefox 19+ */
                text-transform: none;
            }
            :-ms-input-placeholder { /* Internet Explorer 10+ */
                text-transform: none;
            }
            ::placeholder { /* Recent browsers */
                text-transform: none;
            }
            #suggestion-box {
                position: absolute;
                z-index: 3;
            }
            #suggestion-box ul {
                list-style: none;
                margin-left: 2em;
            }
            .search-suggestion {
                box-sizing: border-box;
                padding: 0.5em; 
                background-color: white;
                border: 1px solid black;
                border-bottom: none;
            }
            .search-suggestion:last-child {
                border-bottom-left-radius: 5px;
                border-bottom-right-radius: 5px;
                border-bottom: 1px solid black;
            }
            .search-suggestion:hover {
                cursor: pointer;
                color: var(--boxify-blue);
            }

            #error-messages {
                display: none;
                align-items: center;
                line-height: 0.8em;
                margin: 1em 0.5em 0 0.75em;
                color: red;

                /* Automatically hide after 5 second */
                -moz-animation: autoHide 0s ease-in 5s forwards;
                -webkit-animation: autoHide 0s ease-in 5s forwards;
                -o-animation: autoHide 0s ease-in 5s fowards;
                animation: autoHide 0s ease-in 5s forwards;
                -webkit-animation-fill-mode: forwards;
                animation-fill-mode: forwards;
            }
            #error-messages i {
                margin-right: 0.75em;
            }

            /* Not sure if this is going to work on the second error: https://stackoverflow.com/questions/21993661/css-auto-hide-elements-after-5-seconds */
            @keyframes autoHide {
                to {
                    width: 0;
                    height: 0;
                    margin: 0;
                    overflow: hidden;
                }
            }
            @-webkit-keyframes autoHide {
                to {
                    width: 0;
                    height: 0;
                    margin: 0;
                    visibility: hidden;
                }
            }

            /* SETTINGS */
            input:checked + .slider {
                background-color: var(--boxify-blue);
            }
            #theme {
                padding: 1em;
            }
            #toggle-theme-wrapper {
                display: flex;
                align-items: center;
                justify-content: space-evenly;                
            }

            .info {
                margin-bottom: 0.5em;
            }
            #transcripts, #settings, #downloads, #theme {
                padding: 1em;
            }
            #transcripts, #settings {
                display: none;
            }
            #select-transcripts {
                list-style: none;
            }
            #select-transcripts li {
                display: flex;
                justify-content: space-between;
                padding: 0.5em 1em;
                cursor: pointer;
                border: 1px solid black;
                border-bottom: none;
                color: rgba(0, 0, 0, 0.25);
            }
            #select-transcripts li.selected {
                color: rgba(0, 0, 0, 1);
            }
            #select-transcripts li:first-child {
                border-top-left-radius: 5px;
                border-top-right-radius: 5px;
            }
            #select-transcripts li:last-child {
                border-bottom-left-radius: 5px;
                border-bottom-right-radius: 5px;
                border-bottom: 1px solid black;
            }

            #adjust-size {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            input[type="range"] {
                cursor: pointer;
            }

            .toggle, .color-form {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin: 0.75em 0 0 0;
            }
            input[type="color"] {
                width: 16px;
                height: 16px;
                cursor: pointer;
                margin-right: 7px;
                border-radius: 10px;
                border: none;
            }
            input[type="color"]::-webkit-color-swatch-wrapper {
                padding: 1px;

            }
            input[type="color"]::-webkit-color-swatch {
                border-radius: 10px;
                padding: 2px;
                border: none;
            }

            #reset-settings {
                display: flex;
                align-items: center;
                justify-content: center;
                margin-top: 1em;
                padding-top: 1em;
                cursor: pointer;
                border-top: 1px solid black;
            }
            #reset-settings:hover {
                color: var(--boxify-blue);
            }
            #reset-settings i {
                margin: -3px 0 0 5px;
            }

            #downloads {
                display: none;
                flex-direction: column;
            }
            #downloads button {
                padding: 0.5em 0.25em;
                margin-bottom: 0.2em;
                cursor: pointer;
                border: 1px solid black;
                border-radius: 2px;
            }
            #downloads button:hover {
               color: var(--boxify-blue);
            }
            
            #downloads button:focus {
                outline: none;
            }
            #downloads i {
                margin-right: 0.25em;
            }
            #download-pcr {
                display: none;
            }

            main {
                grid-area: content;
            }


            /* MODELS */
            #genomic-seq {
                font-family: 'Roboto Mono', monospace;
                overflow-x: scroll;
                font-size: 14px;
                margin-top: -25px;
                position: absolute;
            }
            .A, .C, .G, .T {
                padding: 0 1px;
            }
            .A { background: #90ee90; }
            .C { background: #b0c4de; }
            .G { background: #ffec8b; }
            .T { background: #eea2ad; }
            #ruler { 
                visibility: hidden; 
                position: absolute;
            }
            #window {
                position: absolute;
                border: 1px solid red;
                z-index: 3;
            }

            /* PCR */
            #add-primer-button {
                display: none;
                align-items: center;
                cursor: pointer;
                padding: 1em;
            }
            .bi-plus-circle-fill {
                font-size: 1.5em;
                line-height: 0.85em;
                color: var(--boxify-blue);
                margin-right: 0.25em;
            }

            #pcr-wrapper {
                padding: 1em;
                display: grid;
                grid-template-columns: repeat(auto-fit, 350px);
                grid-gap: 1em;
            }
            .no-seq {
                margin-top: -2.5em;
            }
            .pcr {
                border-radius: 5px;
                box-shadow: 4px 4px 4px 1px rgba(0, 0, 0, 0.5);
                padding: 1em;
                font-size: 0.9em;
                border-top: 3px solid transparent;
                align-self: start;
            }
            .primer-input {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 0.25em;
            }
            .pcr input[type="text"] {
                padding: 0.3em;
                border: 1px solid black;
                border-radius: 3px;
            }
            .pcr button {
                color: white;
                border: none;
                border-radius: 3px;
                padding: 0.5em;
                margin-top: 0.5em;
                background-color: var(--boxify-blue);
                cursor: pointer;
                font-size: 1em;
                float: right;
            }
            .pcr button:focus {
                outline: none;
            }

            .given-primers {
                margin-bottom: 1em;
            }
            .pcr-info {
                width: 100%;
                margin-bottom: 0.5em;
                cursor: pointer;
            }
            .pcr-info:last-child {
                margin-bottom: 0;
            }
            .bi-caret-down-fill {
                /* cursor: pointer; */
            }
            .fragment {
                font-size: 0.9em;
                word-break: break-all;
                white-space: normal;
                font-family: Courier New;
                max-height: 5em;
                overflow-y: auto;
                display: none;
            }

            footer {
                grid-area: footer;
            }

            /* Scroll bar customisation */
            ::-webkit-scrollbar { 
                height: 5px;
                width: 5px; 
            }
            ::-webkit-scrollbar-track { border-radius: 5px; }
            ::-webkit-scrollbar-thumb {
                background: gray;
                border-radius: 5px;
            }
        </style>
    </head>
    <body>

        <div class="container">
            <aside>

                <div id="search">
                    <i class="bi-search"></i>
                    <input type="search" id="search-gene" placeholder="Search gene" aria-label="Search for a gene" value="AT3G61860" autofocus>
                    <div id="suggestion-box">
                        <!-- <ul>
                            <li class="search-suggestion">AT1G01010</li>
                            <li class="search-suggestion">AT1G01020</li>
                            <li class="search-suggestion">AT1G01020</li>
                            <li class="search-suggestion">AT1G01020</li>
                            <li class="search-suggestion">AT1G01020</li>
                        </ul> -->
                    </div>
                    <div id="error-messages">
                        <!-- <i class="bi-exclamation-triangle-fill"></i><span>Gene not found!</span> -->
                    </div>
                </div>

                <div id="transcripts">
                    <p class="info">
                        Select transcripts to display and drag to change their order.
                    </p>
                    <ul id="select-transcripts">
                        <!-- <li class="t-id selected" draggable="true"><span>AT3G61860.c1</span><i class="bi-eye"></i></li>
                        <li class="t-id" draggable="true"><span>AT3G61860.c2</span><i class="bi-eye-slash"></i></li>
                        <li class="t-id selected" draggable="true"><span>AT3G61860.P1</span><i class="bi-eye"></i></li>
                        <li class="t-id selected" draggable="true"><span>AT3G61860.ID1</span><i class="bi-eye"></i></li> -->
                    </ul>
                </div>

                <div id="settings">
                    <!-- <h2>Settings <i class="bi-gear"></i></h2> -->

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

                    <div class="color-form">
                        <label for="transcriptColor">Transcript color</label>
                        <input type="color" id="transcriptColor" value="#428bca">
                    </div>

                    <div class="color-form">
                        <label for="cdsColor">CDS color</label>
                        <input type="color" id="cdsColor" value="#51a351">
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

                <canvas id="boxify">
                    Your browser does not support HTML5 support.
                </canvas>
                <div id="genomic-seq"></div>

                <div id="pcr-wrapper">

                    <div id="add-primer-button">
                        <i class="bi-plus-circle-fill"></i> Add primers
                    </div>

                </div>

                <div id="svg-image"></div>

            </main>

            <footer></footer>
        </div>

        <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <script src="assets/js/functions.js"></script>
        <script src="assets/js/canvas-getsvg.js"></script>
        <script src="assets/js/boxify.js"></script>
    </body>
</html>