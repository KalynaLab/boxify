/* GLOBAL DRAWING VARIABLES */
const BOX_HEIGHT = 12;
const HALF_BOX_HEIGHT = BOX_HEIGHT / 2;
const DEFAULT_TOP_MARGIN = 25;
const DEFAULT_LEFT_MARGIN = 125;
const DEFAULT_BORDER_MARGIN = 25;

/* STUFF DO ON PAGE LOAD */
function resetPCR() {
    // Hide previous PCR
    $('.given-primers').empty().hide();
    $('.pcr-results').empty();
    $('.PCR').hide();
    $('.add-primer-button').show();
}

// Clear old localStorage data
localStorage.removeItem('PCR');

/* SETTINGS */
// Expand/Collapse settings
$(document).on('click', '.settings-header', () => {
    $('.settings-content').toggle();
    $(this).find('.caret').html(($('.caret').html().charCodeAt(0) === 9660 ? '&#9650;' : '&#9660;'));
});

// Toggle switch control
$(document).on('click', '.switch', function(e) {
    if ($(e.target).is('input')) {
        $(this).attr('state', ($(this).attr('state') === 'on' ? 'off' : 'on'));
    }
});

// Show/Hide genomic sequence
$(document).on('click', '#show-sequence', function(e) {
    if ($(e.target).is('input')) {
        $('#window').toggle();
        $('#genomic-seq').toggle();
    }
});

// Reorder transcripts
let dragging = null;
const border = 'dashed 2px black',
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
    }
});

// Transcript selection
$(document).on('click', '#select-transcripts button', function() {

    // Make sure at least one transcript is selected
    if ($('#select-transcripts button.list-group-item-dark').length > 1) {
        $(this).toggleClass('list-group-item-dark');
    } else {
        $(this).addClass('list-group-item-dark');
    }

})

// Adjust gene model size
function updateSize(x) {
    $('#slideSize').html(x);
}

// Redraw
$(document).on('click', '#redraw', () => {

    // Select the active transcripts
    let trsIDS = $('#select-transcripts').find('.list-group-item-dark').map((i, e) => { return $(e).html(); }).get();

    boxify(
        parseInt($('#size').val()),
        trsIDS,
        ($('#draw-CDS').attr('state') === 'on' ? true : false),
        $('#transcriptColor').val(),
        $('#cdsColor').val()
    );
    addSeqScroll(trsIDS.length);

});

/* LOAD DATA */
// Autocomplete
$('#form_gene').keyup(function() {
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
    $('#form_gene').val($(this).html());
    loadData();
});

function loadData() {

    // Hide any autocomplete suggestions
    $('#suggestion-box').hide();
    $('#form_gene').blur();

    resetPCR(); // Might not need this function, because I think I'm only calling it once

    let geneID = $('#form_gene').val();
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
                    splicedSeq = '';

                for (let j in exons) {
                    const start = exons[j][0],
                        end = exons[j][1];
                    splicedSeq += seq.slice(start-geneStart, (end-geneStart)+1);
                }

                data['transcripts'][tID]['seq'] = (strand === '+') ? splicedSeq : splicedSeq.split('').reverse().join('');
            }

            // Store the data in localStorage
            localStorage.setItem('data', JSON.stringify(data));

            // Add the genomic sequence to the document and assign class per nucleotide
            $('#genomic-seq').html(seq.split('').map(nt => `<span class='${nt}'>${nt}</span>`).join(''));
            $('#genomic-seq').offset({ top: 'inherit', left: $('#boxify')[0].getBoundingClientRect().left + DEFAULT_LEFT_MARGIN });

            // Draw models
            boxify(
                parseInt($('#size').val()),
                trsIDS,
                ($('#draw-CDS').attr('state') === 'on' ? true : false),
                $('#transcriptColor').val(),
                $('#cdsColor').val()
            );

            addSeqScroll(trsIDS.length);

            // Display settings
            // List the loaded transcripts
            $('#select-transcripts').html('');
            for (let i = 0; i < trsIDS.length; i++) {
                let tID = trsIDS[i];
                $('#select-transcripts').append(`<button type='button' class='list-group-item list-group-item-action list-group-item-dark' draggable='true'>${tID}</button>`);
            }
            
            $('#error_messages').hide();
            $('#settings').show();
            $('.PCR').first().show();
            $('#downloads').show();
        
        } else { // Show error
            $('#error_messages').html(`<strong>Error!</strong> ${data['messages']}`);
            $('#error_messages').show();
        }

    });   

}

$(document).on('click', '#form_submit', (e) => {
    e.preventDefault();
    loadData();
});

/* DRAW STUFF */
function boxify(size, trsIDS, drawTheCDS=true, exonColor='#428bca', cdsColor='#51A351') {

    // Scale the genomic sequence element to the same size as the 
    // transcript models and set the left margin
    $('#genomic-seq').css('width', $('#size').val()-100);

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
            canvasHeight = (nT * (BOX_HEIGHT + HALF_BOX_HEIGHT)) + (data['primers'].length * 10) + (DEFAULT_BORDER_MARGIN * 2) - 6;
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

        let canvas = document.getElementById('boxify');
        canvas.width = canvasWidth;
        canvas.height = canvasHeight;

        const canvasSVGContext = new CanvasSVG.Deferred();
        canvasSVGContext.wrapCanvas(canvas);

        // Start drawing
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Draw each transcript
        for (let i = 0; i < trsIDS.length; i++) {
            let tID = trsIDS[i];
            drawTranscript(tID, data['transcripts'][tID]['exons'], data['scaledExonCoord'], data['gene']['strand'], DEFAULT_BORDER_MARGIN + (i * (BOX_HEIGHT + HALF_BOX_HEIGHT)), exonColor);
        }

        // Draw the CDS
        if (drawTheCDS) {
            for (let i = 0; i < trsIDS.length; i++) {
                tID = trsIDS[i];
                drawCDS(data['transcripts'][tID]['cds'], data['scaledCDSCoord'], DEFAULT_BORDER_MARGIN + (i * (BOX_HEIGHT + HALF_BOX_HEIGHT)), cdsColor);
            }
        }

        // Draw the primer boxes, if any
        const verticalOffset = DEFAULT_BORDER_MARGIN + (nT * (BOX_HEIGHT + HALF_BOX_HEIGHT)),
            primers = data['primers'],
            colors = $('.given-primers').map(function() { return $(this).css('border-color'); }).get();

        for (let i = 0; i < primers.length; i++) {
            drawPrimers(primers[i]['fwd'], primers[i]['rev'], scale, verticalOffset + (i * 10), colors[i]);
        }

        function drawTranscript(tID, coord, scaledCoord, strand, vOffset, color='#428BCA') {

            ctx.font = '12px sans-serif';
            ctx.fillStyle = 'black';
            ctx.textBaseLine = 'top';
            ctx.fillText(tID, DEFAULT_BORDER_MARGIN, vOffset+12, 100);
            ctx.fillStyle = color;
            ctx.strokeStyle = 'rgba(1, 1, 1, 0)';

            let endOfLastExon = 0;
            for (let i in coord) {
                let start = scaledCoord[String(coord[i][0])],
                    end = scaledCoord[String(coord[i][1])];

                // Draw exons
                if (strand === '-' && i == 0) { // Negative strand triangle
                    start += HALF_BOX_HEIGHT;
                    ctx.beginPath();
                    ctx.moveTo(start, vOffset);
                    ctx.lineTo(start - HALF_BOX_HEIGHT, vOffset + HALF_BOX_HEIGHT);
                    ctx.lineTo(start, vOffset + BOX_HEIGHT);
                    ctx.fill();
                } else if (strand === '+' && i == coord.length - 1)  { // Positive strand triangle
                    end -= HALF_BOX_HEIGHT;
                    ctx.beginPath();
                    ctx.moveTo(end, vOffset);
                    ctx.lineTo(end + HALF_BOX_HEIGHT, vOffset + HALF_BOX_HEIGHT);
                    ctx.lineTo(end, vOffset + BOX_HEIGHT);
                    ctx.fill();
                }
                ctx.fillRect(start, vOffset, end - start, BOX_HEIGHT);

                // Draw introns
                if (i > 0 && i < coord.length) {
                    ctx.beginPath();
                    ctx.strokeStyle = color;
                    ctx.moveTo(endOfLastExon, vOffset + HALF_BOX_HEIGHT);
                    ctx.quadraticCurveTo(endOfLastExon, vOffset + HALF_BOX_HEIGHT, start, vOffset + HALF_BOX_HEIGHT);
                    ctx.stroke();
                    ctx.closePath();
                }

                endOfLastExon = end;
            }
        }

        function drawCDS(coordX, scaledCoordX, vOffsetX, color='#51A351') {
            ctx.fillStyle = color;
            ctx.strokeStyle = 'rgba(1, 1, 1, 0)';

            for (let i in coordX) {
                let start = scaledCoordX[String(coordX[i][0])],
                    end = scaledCoordX[String(coordX[i][1])];
                ctx.fillRect(start, vOffsetX, end - start, BOX_HEIGHT);
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
    $('#genomic-seq').scrollLeft(0);

    // Calculate the number of nucleotides
    const ruler = $('#ruler'),
        x = ruler[0].getBoundingClientRect().width - 1, // Account for padding collapse
        w = $('#genomic-seq').width(),
        n = w/x;

    // Generate the window
    const windowWidth = localStorage.getItem('scale') * n,
        windowHeight = nT * (BOX_HEIGHT + HALF_BOX_HEIGHT),
        left = $('#boxify')[0].getBoundingClientRect().left + DEFAULT_LEFT_MARGIN;

    $('#main').append(`<div id='window' style='width: ${windowWidth}px; height: ${windowHeight}px;'></div>`);
    $('#window').offset({ top: DEFAULT_TOP_MARGIN - (BOX_HEIGHT / 4), left: left })

    // See if the window needs to be hidden
    if ($('#show-sequence').attr('state') === 'off') {
        $('#window').hide();
    }
}

// Scroll the window
$('#genomic-seq').on('scroll', () => {
    
    const scrollPerc = $('#genomic-seq').scrollLeft() / ($('#genomic-seq')[0].scrollWidth - $('#genomic-seq').width()),
    px = $('#genomic-seq').width(),
    left = $('#boxify')[0].getBoundingClientRect().left + 125,
    newLeft = left + ((px - $('#window').width()) * scrollPerc);

    $('#window').offset({ top: 'inherit', left: newLeft });   
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
const primerSearchForm = `<form action='' method='POST'>
    <div class='form-group'>
        <label for='fwd-primer' class='pcr-primer-label'>Forward primer</label>
        <input type='text' class='form-control' id='fwd-primer' value='GCGAATTAAGATAAAGATGAGG' onkeyup='this.value = this.value.toUpperCase();'>
    </div>
    <div class='form-group'>
        <label for='rev-primer' class='pcr-primer-label'>Reverse primer</label>
        <input type='text' class='form-control' id='rev-primer' value='GGAAAATTGTCGAGTTTGCG' onkeyup='this.value = this.value.toUpperCase();'>
    </div>
    <button type='submit' class='btn btn-primary' id='primer-search-submit'>Search</button>
</form>`;

$(document).on('click', '.add-primer-button', function(e) {
    $(this).siblings('.enter-form-here').html(primerSearchForm);
    $(this).hide();
});

// Perform PCR
$(document).on('click', '#primer-search-submit', function(e) {
    e.preventDefault();

    const fwd = $('#fwd-primer').val(),
        rev = $('#rev-primer').val(),
        trsIDS = $('#select-transcripts').find('.list-group-item-dark').map((i, e) => { return $(e).html(); }).get();

    let data = JSON.parse(localStorage.getItem('data'));
    let thisPCR = `PCR result for ${fwd} (forward) and ${rev} (reverse) primers.\nIdentifier\tProduct Size\tFragment Sequence\n`;
    const fragments = primerSearch(fwd, rev, trsIDS);
    
    // Create the PCR HTML element
    $(this).parents('.PCR').find('.given-primers').append(`<div class='used-fwd-primer'>Forward: ${fwd}</div>`);
    $(this).parents('.PCR').find('.given-primers').append(`<div class='used-rev-primer'>Reverse: ${fwd}</div>`);
    $(this).parents('.PCR').find('.given-primers').show();
    $(this).parents('.PCR').find('.pcr-results').html('');
    
    for (tID in fragments) {
        $(this).parents('.PCR').find('.pcr-results').append(`<div class='pcr-info'>${tID} (<span class='text-muted'>${fragments[tID]['len']}</span>) <span class='caret d-inline-block align-top'>&#9660;</span><p class='fragment'>${fragments[tID]['product']}</p></div>`);
        thisPCR += `${tID}\t${fragments[tID]['len']}\t${fragments[tID]['product']}\n`;
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
        parseInt($('#size').val()),
        trsIDS,
        ($('#draw-CDS').attr('state') === 'on' ? true : false),
        $('#transcriptColor').val(),
        $('#cdsColor').val()
    );

    // Get rid of the form again and display the next primer search
    $(this).parents().next('.PCR').show();
    $(this).parent().parent('.enter-form-here').html('');

});

// Show/Hide fragments
$(document).on('click', '.pcr-info .caret', function() {
    $(this).parent().find('.fragment').toggle();
    $(this).html(($(this).html().charCodeAt(0) === 9660 ? '&#9650;' : '&#9660;'));
});

/* SAVE FILES */
// Save as PNG
document.querySelector('#download-png').addEventListener('click', (e) => {
    const canvas = document.getElementById('boxify');
    link = e.target;
    link.href = canvas.toDataURL();
    link.download = $('#form_gene').val() + '.png';
});

// Save as SVG
document.querySelector('#download-svg').addEventListener('click', (e) => {
    const canvas = document.getElementById('boxify');
    const ctx = canvas.getContext('2d');
    $('#svg-image').html(ctx.getSVG());
    $('svg').attr('xmlns', 'http://www.w3.org/2000/svg');

    const svgContent = document.getElementById('svg-image').innerHTML,
          blob = new Blob([svgContent], {
              type: 'image/svg+xml'
          }),
          url = window.URL.createObjectURL(blob),
          link = e.target;//.parentElement;

    link.target = '_blank';
    link.download = $('#form_gene').val() + '.svg'; // Change to drawing-data gene_id
    link.href = url;

    // Remove it again
    $('#svg-image').html('');
});

// Save PCR results
document.querySelector('#download-pcr').addEventListener('click', (evt) => {

    const fileContent = localStorage.getItem('PCR'),
        blob = new Blob([fileContent], {
            type: 'text/csv;charset=utf-8;'
        }),
        url = window.URL.createObjectURL(blob),
        link = evt.target;

    link.target = '_blank';
    link.download = `${$('#form_gene').val()}_PCR_results.txt`;
    link.href = url; 
});