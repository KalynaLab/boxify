/* GLOBAL DRAWING VARIABLES */
const BOX_HEIGHT = 14;
const HALF_BOX_HEIGHT = BOX_HEIGHT / 2;
const DEFAULT_TOP_MARGIN = 25;
const DEFAULT_LEFT_MARGIN = 125;
const DEFAULT_BORDER_MARGIN = 25;
const STROKE_WIDTH = 1;

/**
 * ConadjVOffsets an HSL color value to RGB. Conversion formula
 * adapted from http://en.wikipedia.org/wiki/HSL_color_space.
 * Assumes h, s, and l are contained in the set [0, 1] and
 * returns r, g, and b in the set [0, 255].
 *
 * @param   {number}  h       The hue
 * @param   {number}  s       The saturation
 * @param   {number}  l       The lightness
 * @return  {Array}           The RGB representation
*/
function hslToRgb(h, s, l){
    var r, g, b;

    if(s == 0){
        r = g = b = l; // achromatic
    }else{
        var hue2rgb = function hue2rgb(p, q, t){
            if(t < 0) t += 1;
            if(t > 1) t -= 1;
            if(t < 1/6) return p + (q - p) * 6 * t;
            if(t < 1/2) return q;
            if(t < 2/3) return p + (q - p) * (2/3 - t) * 6;
            return p;
        }

        var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        var p = 2 * l - q;
        r = hue2rgb(p, q, h + 1/3);
        g = hue2rgb(p, q, h);
        b = hue2rgb(p, q, h - 1/3);
    }

    return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
}


function css_rgb2hex(rgb){
    var regexp = /^rgb\((\d+),\s+(\d+),\s+(\d+)\)$/g,
        groups = regexp.exec(rgb);
    return '#' + 
        ('0' + parseInt(groups[1],10).toString(16)).slice(-2) +
        ('0' + parseInt(groups[2],10).toString(16)).slice(-2) +
        ('0' + parseInt(groups[3],10).toString(16)).slice(-2);
}

function rgb2hex(r, g, b) {
    return '#' +
        ('0' + r.toString(16)).slice(-2) +
        ('0' + g.toString(16)).slice(-2) +
        ('0' + b.toString(16)).slice(-2);
}

function getRandomHEX() {
    // https://www.w3schools.com/colors/colors_hsl.asp
    var h = 360 * Math.random();
    var s = 100 + 70 * Math.random();
    var l = 175 + 25 * Math.random();
    
    rgb = hslToRgb(h/360, s/100, l/100);
    return rgb2hex(rgb[0], rgb[1], rgb[2]);
} 


/* STUFF DO ON PAGE LOAD */
function resetPCR() {
    $('.pcr').each(function() { $(this).remove(); });
    $('#add-primer-button').css('display', 'flex');
}

// Clear old localStorage data
['data', 'scale', 'PCR'].forEach(k => localStorage.removeItem(k));

/* SETTINGS */
// Set light/dark theme based on the hour of the day
// Dark theme between 6PM and 8AM
const current = new Date();
if (current.getHours() < 8 || current.getHours() >= 18) {
    $('#toggle-theme').attr('state', 'on');
    $('#toggle-theme').click();
    toggleTheme();
}

// Toggle switch control
$(document).on('click', '.switch', function(e) {
    if ($(e.target).is('input')) {
        $(this).attr('state', ($(this).attr('state') === 'on' ? 'off' : 'on'));
        if ($(this).attr('id') === 'toggle-theme') {
            toggleTheme();
        }
    }
});

// Show/Hide genomic sequence
$(document).on('click', '#show-sequence', function(e) {
    if ($(e.target).is('input')) {
        $('#window, #expand-seq, #genomic-seq').toggle();
        // $('#pcr-wrapper').toggleClass('no-seq');

        if ($('#expand-seq').hasClass('bi-caret-up-fill')) {
            $('#expand-seq').removeClass('bi-caret-up-fill').addClass('bi-caret-down-fill');
            $('#display-seq').hide();
        }

        addSeqScroll($('#select-transcripts').find('li.selected').length);
    }
});

// Toggle light and dark mode
function toggleTheme() {
    if ($('#toggle-theme').attr('state') === 'on') {
        $('head').append( $(`<link rel="stylesheet" type="text/css"/>`).attr('href', 'assets/css/dark.css') );
    } else {
        $('link[rel=stylesheet][href="assets/css/dark.css"]').remove();
    }
    redraw();
}

// Reorder transcripts
let dragging = null;
const border = 'dashed 2px',
    dragParent = 'select-transcripts';

// dragstart
document.addEventListener('dragstart', (e) => {
    dragging = e.target;
    e.dataTransfer.setData('text/html', dragging);
});

// dragover
document.addEventListener('dragover', (e) => {
    e.preventDefault();
    if ( $(e.target).parent()[0]['id'] === dragParent ) {
        const bounding = e.target.getBoundingClientRect(),
            offset = bounding.y + (bounding.height / 2);

        if (e.clientY - offset > 0) {
            e.target.style['border-bottom'] = border;
            e.target.style['border-top'] = '';
        } else {
            e.target.style['border-bottom'] = '';
            e.target.style['border-top'] = border;
        }
    }
});

// dragleave
document.addEventListener('dragleave', (e) => {
    e.target.style['border-bottom'] = '';
    e.target.style['border-top'] = '';
});

// dragdrop
document.addEventListener('drop', (e) => {
    e.preventDefault();

    if ($(e.target).parent()[0]['id'] === dragParent) {
        if (e.target.style['border-bottom'] !== '') {
            e.target.style['border-bottom'] = '';
            e.target.parentNode.insertBefore(dragging, e.target.nextSibling);
        } else {
            e.target.style['border-top'] = '';
            e.target.parentNode.insertBefore(dragging, e.target);
        }
        redraw();
    }
});

$(document).on('click', '#expand-seq', function() {
    if ($(this).hasClass('bi-caret-down-fill')) {
        $(this).removeClass('bi-caret-down-fill').addClass('bi-caret-up-fill');
    } else {
        $(this).removeClass('bi-caret-up-fill').addClass('bi-caret-down-fill');
    }
    $('#display-seq').toggle();
});

// Transcript selection
$(document).on('click', '#select-transcripts li', function() {

    // Toggle upper/lowercase sequence and possible PCR result
    let clickedT = $(this).find('span').html();

    if ($('#select-transcripts li.selected').length > 1) {
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
            $(this).find('i').removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            $(this).addClass('selected');
            $(this).find('i').removeClass('bi-eye-slash').addClass('bi-eye');
        }
        $(`[data-transcript-id="${clickedT}"]`).toggle();
        redraw();
    } else {
        $(this).addClass('selected');
        $(this).find('i').removeClass('bi-eye-slash').addClass('bi-eye');
        redraw();
        $(`[data-transcript-id="${clickedT}"]`).show();
    }

});

// Adjust gene model size
$(document).on('change', '#size', () => {
    $('#slideSize').html($('#size').val());
    redraw();
});

// Redraw
function redraw() {
    // Select the active transcripts
    let trsIDS = $('#select-transcripts').find('li.selected > span').map((i, e) => { return $(e).html(); }).get();
    
    if (trsIDS.length) {
        boxify(
            'boxify',
            parseInt($('#size').val()),
            trsIDS,
            ($('#draw-CDS').attr('state') === 'on' ? true : false),
            $('#transcript-fill-color').val(),
            $('#transcript-stroke-color').val(),
            $('#cds-fill-color').val(),
            $('#cds-stroke-color').val(),
            ($('#toggle-theme').attr('state') === 'on' ? '#ddd' : 'black')
        );
        addSeqScroll(trsIDS.length);
    }
}

/* LOAD DATA */
// Autocomplete
$('#search-gene').keyup(function() {
    if ($(this).val().length > 2) {
        $.ajax({
            type: 'POST',
            url: 'assets/ajax/autocomplete.php',
            data:  `term=${$(this).val()}`,
            beforeSend: function() {
                $('#suggestion-box').html('');
            },
            success: function(data) {
                $('#suggestion-box').show();
                $('#suggestion-box').html(data);
            }
        });
    } else {
        $('#suggestion-box').hide();
    }
});

$(document).on('click', '.search-suggestion', function(e) {
    $('#search-gene').val($(this).html());
    loadData();
});

function loadData() {

    // Hide any autocomplete suggestions
    $('#suggestion-box').hide();
    $('#search-gene').blur();

    let geneID = $('#search-gene').val();
    console.log(geneID);

    // Data retrieval and drawing
    $.post('assets/ajax/get_data.php', { gene: geneID }).done((output) => {

        data = $.parseJSON(output);

        // On success
        if (data['okay']) {

            data['gene']['geneID'] = geneID;
            const trsIDS = Object.keys(data['transcripts']),
                geneStart = parseInt(data['gene']['start']),
                strand = data['gene']['strand'],
                seq = (strand === '+') ? data['gene']['seq'] : data['gene']['seq'].split('').reverse().join('');

            // Get the spliced sequences
            for (let i = 0; i < trsIDS.length; i++) {
                let tID = trsIDS[i],
                    exons = data['transcripts'][tID]['exons'],
                    splicedSeq = '',
                    displaySeq = ' '.repeat((exons[0][0] - geneStart - 1 >= 0 ? exons[0][0] - geneStart - 1 : exons[0][0] - geneStart));

                for (let j in exons) {
                    const start = exons[j][0],
                        end = exons[j][1],
                        relStart = (start - geneStart - 1 < 0 ? start - geneStart : start - geneStart - 1);

                    if (j > 0) { 
                        const intronStart = exons[(j - 1)][1],
                            intronEnd = exons[j][0] - 1;
                        displaySeq += seq.slice(intronStart - geneStart, intronEnd - geneStart).toLowerCase();
                    }

                    splicedSeq += seq.slice(relStart, (end - geneStart));
                    displaySeq += seq.slice(relStart, (end - geneStart)).toUpperCase();
                }

                data['transcripts'][tID]['seq'] = (strand === '+') ? splicedSeq : splicedSeq.split('').reverse().join('');
                data['transcripts'][tID]['display-seq'] = `<div class="display-seq" data-transcript-id="${tID}">${displaySeq.split('').map(nt => `<pre class='X'>${nt}</pre>`).join('')}</div>`;
                //$('#display-seq').append(`<div class="display-seq" data-transcript-id="${tID}">${displaySeq.split('').map(nt => `<pre class='X'>${nt}</pre>`).join('')}</div>`);
            }

            // Store the data in localStorage
            localStorage.setItem('data', JSON.stringify(data));


            // Add the genomic sequence to the document and assign class per nucleotide
            $('#genomic-seq').html(seq.split('').map(nt => `<span class='${nt}'>${nt}</span>`).join(''));
            $('#sequences').offset({ left: $('main')[0].getBoundingClientRect().left + DEFAULT_LEFT_MARGIN });
            $('#expand-seq').show();

            // Draw models
            boxify(
                'boxify',
                parseInt($('#size').val()),
                trsIDS,
                ($('#draw-CDS').attr('state') === 'on' ? true : false),
                $('#transcript-fill-color').val(),
                $('#transcript-stroke-color').val(),
                $('#cds-fill-color').val(),
                $('#cds-stroke-color').val(),
                ($('#toggle-theme').attr('state') === 'on' ? '#ddd' : 'black')
            );

            // Display settings
            // List the loaded transcripts
            $('#select-transcripts').html('');
            for (let i = 0; i < trsIDS.length; i++) {
                let tID = trsIDS[i];
                // $('#select-transcripts').append(`<button type='button' class='list-group-item list-group-item-action list-group-item-dark' draggable='true'>${tID}</button>`);
                $('#select-transcripts').append(`<li class="t-id selected" draggable="true"><span>${tID}</span><i class="bi-eye"></i></span></li>`);
            }
            
            $('#error-messages').hide();
            $('#transcripts, #settings').show();
            resetPCR(); // Might not need this function, because I think I'm only calling it once
            $('#downloads').css('display', 'flex');
            addSeqScroll(trsIDS.length);
        
        } else { // Show error
            $('#error-messages').html(`<i class="bi-exclamation-triangle-fill"></i><span>${data['messages']}</span>`);
            $('#error-messages').css('display', 'flex');
            $('#error-messages').show();
        }

    });   

}

// press enter to load data in search bar
$(document).keypress((e) => {
    let keycode = (e.keyCode ? e.keyCode : e.which);
    if (keycode === 13) {
        e.preventDefault;
        loadData();
    }
});

/* DRAW STUFF */
function boxify(elementID, size, trsIDS, drawTheCDS=true, exonFill='#428bca', exonStroke='#428bca', cdsFill='#51A351', cdsStroke='#51A351', fontColor='black') {

    // Scale the genomic sequence element to the same size as the 
    // transcript models and set the left margin
    $('#sequences, #genomic-seq, #display-seq').css('width', $('#size').val()-100);

    // Get data
    data = JSON.parse(localStorage.getItem('data'));
    const scale = (size-100) / (data['exonCoord'][data['exonCoord'].length - 1] - data['exonCoord'][0]),
        firstCoord = data['exonCoord'][0],
        nT = trsIDS.length;
    localStorage.setItem('scale', scale);

    // Scale the coordinates
    data['scaledExonCoord'] = Object.assign({}, ...data['exonCoord'].map( (x) => ({[String(x)]: DEFAULT_LEFT_MARGIN + Math.round((x - firstCoord) * scale)})));
    data['scaledCDSCoord'] = Object.assign({}, ...data['cdsCoord'].map( (x) => ({[String(x)]: DEFAULT_LEFT_MARGIN + Math.round((x - firstCoord) * scale)})));

    // Initialize canvas
    window.addEventListener('load', eventWindowLoaded(), false);
    const Debugger = () => {};
    Debugger.log = (message) => {
        try {
            console.log(message);
        } catch (exception) {
            return;
        }
    }

    function eventWindowLoaded() {
        const canvasWidth = size + (DEFAULT_BORDER_MARGIN * 2),
            canvasHeight = (nT * (BOX_HEIGHT + HALF_BOX_HEIGHT)) + (data['primers'].length * 10) + (DEFAULT_BORDER_MARGIN * 2) - HALF_BOX_HEIGHT;
        canvasApp(canvasWidth, canvasHeight);
    }

    function canvasSupport() {
        return !!document.createElement('canvas').getContext;
    }

    function canvasApp(canvasWidth, canvasHeight) {

        // Test if HTML5 Canvas is supported
        if (!canvasSupport()) {
            return;
        }

        let canvas = document.getElementById(elementID);
        canvas.width = canvasWidth;
        canvas.height = canvasHeight;

        const canvasSVGContext = new CanvasSVG.Deferred();
        canvasSVGContext.wrapCanvas(canvas);

        // Start drawing
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.translate(0.5, 0.5);

        // Draw each transcript
        for (let i = 0; i < trsIDS.length; i++) {
            let tID = trsIDS[i];
            drawTranscript(tID, data['transcripts'][tID]['exons'], data['scaledExonCoord'], data['gene']['strand'], DEFAULT_BORDER_MARGIN + (i * (BOX_HEIGHT + HALF_BOX_HEIGHT)), exonFill, exonStroke, fontColor);
        }

        // Draw the CDS
        if (drawTheCDS) {
            for (let i = 0; i < trsIDS.length; i++) {
                tID = trsIDS[i];
                drawCDS(data['transcripts'][tID]['cds'], data['scaledCDSCoord'], DEFAULT_BORDER_MARGIN + (i * (BOX_HEIGHT + HALF_BOX_HEIGHT)), cdsFill, cdsStroke);
            }
        }

        // Draw the primer boxes, if any
        const adjVOffseticalOffset = DEFAULT_BORDER_MARGIN + (nT * (BOX_HEIGHT + HALF_BOX_HEIGHT)),
            primers = data['primers'],
            // colors = $('.given-primers').map(function() { return $(this).css('border-color'); }).get();
            colors = $('.pcr').map(function() { return $(this).css('border-top-color'); }).get();

        for (let i = 0; i < primers.length; i++) {
            drawPrimers(primers[i]['fwd'], primers[i]['rev'], scale, adjVOffseticalOffset + (i * 10) - STROKE_WIDTH, css_rgb2hex(colors[i]));
        }

        function drawTranscript(tID, coord, scaledCoord, strand, vOffset, fillColor="#428BCA", strokeColor="#428BCA", fontColor="#000") {

            // Transcript ID
            ctx.font = '12px sans-serif';
            ctx.fillStyle = fontColor;
            ctx.txtBaseLine = 'top';
            ctx.fillText(tID, DEFAULT_BORDER_MARGIN, vOffset+10, 100);

            // Boxes
            ctx.lineJoin = "round";
            ctx.fillStyle = fillColor;
            ctx.strokeStyle = strokeColor;

            let endOfLastExon = 0;
            for (let i in coord) {
                let start = scaledCoord[String(coord[i][0])],
                    end = scaledCoord[String(coord[i][1])];

                // Draw exons
                const half = (end - start >= HALF_BOX_HEIGHT ? HALF_BOX_HEIGHT : end - start); // Account for exons shorter than HALF_BOX_HEIGHT
                start += STROKE_WIDTH;
                end -= STROKE_WIDTH;

                const adjVOffset = vOffset + STROKE_WIDTH;
                const adjHBH = HALF_BOX_HEIGHT - STROKE_WIDTH;
                const adjBH = BOX_HEIGHT - 2 * STROKE_WIDTH;

                if (strand === '-' && i == 0) { // Last exon of antisense transcript
                    ctx.beginPath();
                    ctx.moveTo(start, vOffset + adjHBH);
                    ctx.lineTo(start + half, vOffset);
                    ctx.lineTo(end, vOffset);
                    ctx.lineTo(end, vOffset + adjBH);
                    ctx.lineTo(start + half, vOffset + adjBH);
                    ctx.lineTo(start, vOffset + adjHBH);
                    ctx.closePath();
                    ctx.fill();
                    ctx.stroke();
                } else if (strand === '+' && i == coord.length - 1) { // Last exon of sense transcript
                    ctx.beginPath();
                    ctx.moveTo(start, vOffset);
                    ctx.lineTo(end - half, vOffset);
                    ctx.lineTo(end, vOffset + adjHBH);
                    ctx.lineTo(end - half, vOffset + adjBH);
                    ctx.lineTo(start, vOffset + adjBH);
                    ctx.lineTo(start, vOffset);
                    ctx.closePath();
                    ctx.fill();
                    ctx.stroke();
                } else {
                    ctx.beginPath();
                    ctx.moveTo(start, vOffset); 
                    ctx.lineTo(end, vOffset);
                    ctx.lineTo(end, vOffset + adjBH);
                    ctx.lineTo(start, vOffset + adjBH);
                    ctx.lineTo(start, vOffset);
                    ctx.closePath();
                    ctx.fill();
                    ctx.stroke();
                }

                // Draw introns
                if (i > 0 && i < coord.length) {
                    ctx.beginPath();
                    ctx.moveTo(endOfLastExon, vOffset + HALF_BOX_HEIGHT - STROKE_WIDTH);
                    ctx.quadraticCurveTo(endOfLastExon, vOffset + HALF_BOX_HEIGHT - STROKE_WIDTH, start, vOffset + HALF_BOX_HEIGHT - STROKE_WIDTH);
                    ctx.stroke();
                    ctx.closePath();
                }

                endOfLastExon = end;

            }

        }

        function drawCDS(coordX, scaledCoordX, vOffsetX, fillColor="#51A351", strokeColor="#51A351") {

            ctx.fillStyle = fillColor;
            ctx.strokeStyle = strokeColor;
            const adjBH = BOX_HEIGHT - 2 * STROKE_WIDTH;

            for (let i in coordX) {
                let start = scaledCoordX[String(coordX[i][0])],
                    end = scaledCoordX[String(coordX[i][1])];

                start += STROKE_WIDTH;
                end -= STROKE_WIDTH;
                
                ctx.beginPath();
                ctx.moveTo(start, vOffsetX); 
                ctx.lineTo(end, vOffsetX);
                ctx.lineTo(end, vOffsetX + adjBH);
                ctx.lineTo(start, vOffsetX + adjBH);
                ctx.lineTo(start, vOffsetX);
                ctx.closePath();
                ctx.fill();
                ctx.stroke();

            }

        }

        function drawPrimers(fwd, rev, scaleFactor, vOffsetX, color) {
            ctx.fillStyle = color;
            ctx.fillRect(DEFAULT_LEFT_MARGIN + fwd[0] * scaleFactor, vOffsetX, (fwd[1] - fwd[0]) * scaleFactor, 5);
            ctx.fillRect(DEFAULT_LEFT_MARGIN + rev[0] * scaleFactor, vOffsetX, (rev[1] - rev[0]) * scaleFactor, 5);
        }
    }

}

// Create the sequence window. Determine the number of displayed 
// dinucleotides based on the width of a single-character hidden
// element
function addSeqScroll(nT) {

    // Remove any potentially existing window and reset scrolling
    $('#window').remove();
    $('#sequences').scrollLeft(0);

    // Calculate the number of nucleotides via a temporary 
    // element with a nucleotide class
    const ruler = document.createElement('div');
    ruler.id = 'ruler';
    ruler.className = 'A';
    ruler.innerHTML = 'X';
    document.body.appendChild(ruler);

    const x = ruler.getBoundingClientRect().width - 1, // Account for padding collapse
        w = parseInt($('#size').val()) - 100,
        n = w/x;
    document.body.removeChild(ruler);

    // Generate the window
    const windowWidth = localStorage.getItem('scale') * n,
        windowHeight = nT * (BOX_HEIGHT + HALF_BOX_HEIGHT),
        left = $('#boxify')[0].getBoundingClientRect().left + DEFAULT_LEFT_MARGIN;

    $('main').append(`<div id='window' style='width: ${windowWidth}px; height: ${windowHeight}px;'></div>`);
    $('#window').offset({ top: DEFAULT_TOP_MARGIN - (BOX_HEIGHT / 4), left: left })

    // Place the expand-seq caret
    const seqRect = $('#boxify')[0].getBoundingClientRect();
    $('#expand-seq').offset({ top: seqRect.height - 20, left: seqRect.left + 100 });

    // See if the window needs to be hidden
    if ($('#show-sequence').attr('state') === 'off') {
        $('#window, #expand-seq, #display-seq').hide();
    }

    // Add the display-seq
    data = JSON.parse(localStorage.getItem('data'));
    visibleIDS = $('#select-transcripts').find('li.selected > span').map((i, e) => { return $(e).html(); }).get(),
    trsIDS = $('#select-transcripts').find('li > span').map((i, e) => { return $(e).html(); }).get();

    $('#display-seq').html('');
    for (let i = 0; i < trsIDS.length; i++) {
        let tID = trsIDS[i];
        $('#display-seq').append(data['transcripts'][tID]['display-seq']);

        // Hide
        if (!visibleIDS.includes(tID)) {
            $(`[data-transcript-id="${tID}"]`).hide();
        }
    }

}

// Scroll the window
$('#sequences').on('scroll', () => {
    
    const scrollPerc = $('#sequences').scrollLeft() / ($('#sequences')[0].scrollWidth - $('#sequences').width()),
    px = $('#genomic-seq').width(),
    left = $('#boxify')[0].getBoundingClientRect().left + 125,
    newLeft = left + ((px - $('#window').width()) * scrollPerc);

    $('#window').offset({ top: 'inherit', left: newLeft });   
});

/* AUTO-REDRAW */
// On CDS change
$(document).on('change', '#draw-CDS, #transcript-fill-color, #transcript-stroke-color, #cds-fill-color, #cds-stroke-color', () => {
    redraw();
});


/* PCR STUFF */
function primerSearch(fwdPrimer, revPrimer, transcripts) {
    
    const rx = new RegExp(`${fwdPrimer}.*?(?=${revcom(revPrimer)})`);
    let fragments = new Array;

    // Add the genomic DNA sequence to localStorage
    data = JSON.parse(localStorage.getItem('data'));
    data['transcripts']['Genomic DNA'] = { 'seq': data['gene']['seq'] };
    trsIDS = Array.from(transcripts);
    trsIDS.unshift('Genomic DNA');
    localStorage.setItem('data', data);

    // Look for fragments
    for (let tID of trsIDS) {
        if (rx.test(data['transcripts'][tID]['seq'])) {
            m = data['transcripts'][tID]['seq'].match(rx);
            fragments[tID] = { 'product': m[0] + revcom(revPrimer), 'offset': m['index'], 'len': (m[0] + revcom(revPrimer)).length }
        } else {
            fragments[tID] = { 'product': 'No product found', offset: -1, 'len': '#NA' }
        }
    }

    return fragments
}

// Primer form to append
const insertPCR = `<div class="pcr">
    <form action="" method="POST">
        <div class="primer-input">
            <label for="fwd-primer">Forward primer</label>
            <input type="text" id="fwd-primer" value="GCGAATTAAGATAAAGATGAGG">
        </div>
        <div class="primer-input">
            <label for="rev-primer">Reverse primer</label>
            <input type="text" id="rev-primer" value="GGAAAATTGTCGAGTTTGCG">
        </div>
        <button type="submit" id="primer-search-submit">Submit</button>
    </form>
    <div class="given-primers"></div>
    <div class="pcr-results"></div>
</div>`;

$(document).on('click', '#add-primer-button', function(e) {

    // Only add another PCR form if the last one has run
    if ($('.pcr').length === 0 || $('.given-primers').last().html().length > 0) {

        // Remove the form from the last pcr element
        $(insertPCR).insertBefore('#add-primer-button');
        $('.pcr').last().css('border-top', `3px solid ${getRandomHEX()}`);
        $('#add-primer-button').hide();
    }
});

// Perform PCR
$(document).on('click', '#primer-search-submit', function(e) {
    e.preventDefault();

    const fwd = $('#fwd-primer').val(),
        rev = $('#rev-primer').val(),
        visibleIDS = $('#select-transcripts').find('li.selected > span').map((i, e) => { return $(e).html(); }).get(),
        trsIDS = $('#select-transcripts').find('li > span').map((i, e) => { return $(e).html(); }).get();

    // Make sure both primers are given
    if (fwd.length && rev.length) {

        let data = JSON.parse(localStorage.getItem('data'));
        let thisPCR = `PCR result for ${fwd} (forward) and ${rev} (reverse) primers.\nIdentifier\tProduct Size\tFragment Sequence\n`;
        const fragments = primerSearch(fwd, rev, trsIDS);
        
        // Create the PCR HTML element
        $(this).parents('.pcr').find('.given-primers').append(`<div class='used-fwd-primer'>Forward: ${fwd}</div>`);
        $(this).parents('.pcr').find('.given-primers').append(`<div class='used-rev-primer'>Reverse: ${rev}</div>`);
        $(this).parents('.pcr').find('.given-primers').show();
        $(this).parents('.pcr').find('.pcr-results').html('');
        
        for (tID in fragments) {
            $(this).parents('.pcr').find('.pcr-results').append(`<div class='pcr-info' data-transcript-id='${tID}'><span>${tID} (${fragments[tID]['len']})</span> <i class='bi-caret-down-fill'></i><p class='fragment'>${fragments[tID]['product']}</p></div>`);
            thisPCR += `${tID}\t${fragments[tID]['len']}\t${fragments[tID]['product']}\n`;

            // Hide
            if (tID !== 'Genomic DNA' && !visibleIDS.includes(tID)) {
                $(`[data-transcript-id="${tID}"]`).hide();
            }
        }

        // Store the PCR result in localStorage
        let existingPCR = localStorage.getItem('PCR');
        let updatedPCR = existingPCR ? existingPCR + thisPCR : thisPCR;
        localStorage.setItem('PCR', updatedPCR);

        // Show download link
        $('#download-pcr').show();

        // Calculate the primer positions and add to canvas
        let absStart = 0,
            primerPos = {};
        if (data['gene']['strand'] === '+') {
            absStart = fragments['Genomic DNA']['offset'];
            primerPos = {
                'fwd': [ absStart, absStart + fwd.length ],
                'rev': [ absStart + fragments['Genomic DNA']['len'] - rev.length, absStart + fragments['Genomic DNA']['len'] ]
            };
        } else {
            absStart = data['gene']['seq'].length - (fragments['Genomic DNA']['offset'] + fragments['Genomic DNA']['len']);
            primerPos = {
                'fwd': [ absStart, absStart + rev.length ],
                'rev': [ absStart + (fragments['Genomic DNA']['len'] - rev.length), absStart + fragments['Genomic DNA']['len'] ]
            };
        }

        data['primers'].push(primerPos);
        localStorage.setItem('data', JSON.stringify(data));

        boxify(
            'boxify',
            parseInt($('#size').val()),
            visibleIDS,
            ($('#draw-CDS').attr('state') === 'on' ? true : false),
            $('#transcript-fill-color').val(),
            $('#transcript-stroke-color').val(),
            $('#cds-fill-color').val(),
            $('#cds-stroke-color').val(),
            ($('#toggle-theme').attr('state') === 'on' ? '#ddd' : 'black')
        );
        addSeqScroll(visibleIDS.length);

        // Remove the form from the last pcr element, so I do not get element
        // with duplicate IDs
        $('.pcr').last().find('form').remove();
        $('#add-primer-button').css('display', 'flex');

    } else {

        if (fwd.length === 0) {
            $('#fwd-primer').css('border-color', ($('#toggle-theme').attr('state') === 'on' ? '#980000' : 'red'));
        }
        if (rev.length === 0) {
            $('#rev-primer').css('border-color', ($('#toggle-theme').attr('state') === 'on' ? '#980000' : 'red'));
        }

    }

});

// Show/Hide fragments
$(document).on('click', '.pcr-info', function() {
    $(this).find('.fragment').toggle();
    if ($(this).find('i').hasClass('bi-caret-down-fill')) {
        $(this).find('i').removeClass('bi-caret-down-fill').addClass('bi-caret-up-fill');
    } else {
        $(this).find('i').removeClass('bi-caret-up-fill').addClass('bi-caret-down-fill');
    }
});

/* RESET SETTINGS */
$(document).on('click', '#reset-settings', () => {

    // Select all transcripts
    $('#select-transcripts').find('li').each(function(i) {
        $(this).addClass('selected');
        $(this).find('i').removeClass('bi-eye-slash').addClass('bi-eye');
        $(`[data-transcript-id="${$(this).find('span').html()}"]`).show();
    });

    // Set size back to 800px
    $('#slideSize').html(800);
    $('#size').val(800);

    // Turn CDS drawing on
    if ($('#draw-CDS').attr('state') === 'off') {
        $('#draw-CDS').click();
        $('#draw-CDS').attr('state', 'on');
    }

    // Display sequence
    if ($('#show-sequence').attr('state') === 'off') {
        $('#show-sequence').click();
        $('#show-sequence').attr('state', 'on');
        $('#window, #expand-seq, #genomic-seq').show();
        // $('#pcr-wrapper').removeClass('no-seq');
    }
    $('#expand-seq').removeClass('bi-caret-up-fill').addClass('bi-caret-down-fill');
    $('#display-seq').hide();

    // Reset transcript and CDS colors
    $('#transcript-fill-color, #transcript-stroke-color').val('#428bca');
    $('#cds-fill-color, #cds-stroke-color').val('#51a351');

    redraw();

});

/* SAVE FILES */
// Save as PNG
document.querySelector('#download-png').addEventListener('click', (e) => {

    boxify(
        'save-image',
        parseInt($('#size').val()),
        $('#select-transcripts').find('li.selected > span').map((i, e) => { return $(e).html(); }).get(),
        ($('#draw-CDS').attr('state') === 'on' ? true : false),
        $('#transcript-fill-color').val(),
        $('#transcript-stroke-color').val(),
        $('#cds-fill-color').val(),
        $('#cds-stroke-color').val(),
        'black'
    );

    const canvas = document.getElementById('save-image');
    const a = document.createElement('a');
    a.href = canvas.toDataURL();
    a.download = $('#search-gene').val() + '.png';

    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);

});

// Save as SVG
document.querySelector('#download-svg').addEventListener('click', (e) => {

    boxify(
        'save-image',
        parseInt($('#size').val()),
        $('#select-transcripts').find('li.selected > span').map((i, e) => { return $(e).html(); }).get(),
        ($('#draw-CDS').attr('state') === 'on' ? true : false),
        $('#transcript-fill-color').val(),
        $('#transcript-stroke-color').val(),
        $('#cds-fill-color').val(),
        $('#cds-stroke-color').val(),
        'black'
    );

    const canvas = document.getElementById('save-image');
    const ctx = canvas.getContext('2d');
    $('#save-image').html(ctx.getSVG());
    $('svg').attr('xmlns', 'http://www.w3.org/2000/svg');

    const svgContent = document.getElementById('save-image').innerHTML,
          blob = new Blob([svgContent], {
              type: 'image/svg+xml'
          }),
          url = window.URL.createObjectURL(blob),
          link = document.createElement('a');

    link.target = '_blank';
    link.download = $('#search-gene').val() + '.svg'; // Change to drawing-data gene_id
    link.href = url;

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Remove it again
    $('#save-image').html('');
});

// Save PCR results
document.querySelector('#download-pcr').addEventListener('click', (evt) => {

    const fileContent = localStorage.getItem('PCR'),
        blob = new Blob([fileContent], {
            type: 'text/csv;charset=utf-8;'
        }),
        url = window.URL.createObjectURL(blob),
        link = document.createElement('a');

    link.target = '_blank';
    link.download = `${$('#search-gene').val()}_PCR_results.txt`;
    link.href = url; 

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
});