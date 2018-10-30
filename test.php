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
        <style>

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
                            <div class="list-group text-center" id="select-transcripts">
                            </div>

                            <div id="size">600</div>
                            <button type="button" class="btn" id="redraw">Redraw</button>
                        </div>
                    </div>
                </div>
                <div class="col" id="main">

            </div>
        </div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="assets/js/js.cookie.js"></script>
        <script src="assets/js/functions.js"></script>

        <script type="text/javascript">
            var f = "GCGAATTAAGATAAAGATGAGG",
                r = "GGAAAATTGTCGAGTTTGCG",
                genomic = "CGACGGAAAATGGGTCTGTATATAAACATGTATAAATTTAATTCTATAAGAAAAGACTCCAACGAGTTCTTCGGTATGGGGCTAACTTATTGTGGGCCTATTTATAAAGAAGGCCCATTATGATTTTTGAATTTCCGAATGTACCCATGATGGTATGACGGGCTTTTTTAAGTCAACCAGGCTCTGCTTTTCTTCGATGAGCGTAGTAGTCGTCGTCGTCGTCTAGGGTTTGTTTTTCGTTTCTTCTCCGATTGTTCAGAGGTAACTTTTCTCCGATAATTATACTTGCTCATGTCCATATACCTTATTTTATCCCTTACATGTTACTAATTTCGAGTTATTGGATCGTTAGGAATTGCGAATTAAGATAAAGATGAGGCCAGTGTTCGTCGGCAATTTCGAGTATGAAACTCGCCAGTCGGATCTGGAACGGTTGTTCGACAAGTATGGGAGAGTCGACCGAGTGGACATGAAATCTGGTAAAGTGTCTTCTAAGTTATACGCCGGTGAAATTTTGTTGTTTGTCCGTCTCGAATTCGAATTCGTCTTCTGGTTGATAAATCATCTTTGCTTAGGTTTAGTTTCTGCTTGACCTAGGATTCTGGATACTTAGAACTAGTTCCAGTTTCATACATGATTTGAAGGTTTATATATAGTACCGTTACATAGAAAAGATTGGTTTTATTTTTCCCGAATGTGTTAAGTGTGGTAGGGGGTTTATGCAGTAGAGAGGATGGTTGTATTGAGTGTTTATTTGTTCGCAGGAACTTTTCTTCAAAGGAACAAGCGAATTAAGATAAAGATGAGGGATTTGCCATCTTCTTCTTCTTCCTGCAAAATCATTTCTACATTTTCATACCTGCCTTTATCATCTTGTGCCTACCAATCTTCACTGACTCTAGAGAAACTTTGGAATTTGTCTGTCTATTACCAATCATCGATGCTTCAGATGGAAGTCTATTGAATCCATCATTGCCATGCCTTGCCTTCCATATCTGAACTCCTCCACTCAGAATGTTTAAGCATGCCTGGCAAATCACTCAGATTTTTTAATATAAAATTGGTGTGCGAATTAAGATAAAGATGAGGTTTTAAAAATAAAAATCCTGAATTCCTGTGTTATGCTACAAATCTTGTTTTTCTACTACATCATGGAATGCCTCTACATCTCTCATATTCACCAGCCTTCACTTATATGAAGGTGTTGTATATCTATTTAGTTATTTTCTAGGGTAAACGAGATATGCAGCAGGTTAATGGACAGGAGGATTGTCACTCTTTGTGTTGCGTTTTGTAATCCTTGTGCTTTCTTTTGTTGCAGGATATGCTTTTGTGTACTTTGAGGATGAACGTGATGCTGAAGACGCTATTCGCAAACTCGACAATTTTCCTTTTGGATATGAGAAACGCAGGTTATCAGTTGAATGGGCAAAGGTATAAATTTTTTTCTGGTTTTTATTGAAGCTTGCACTATGCCGCATATTTAAGAAACCTTTTAAAGTTTCTTTCGGTTTTCAGGGTGAACGTGGCAGGCCTCGTGGTGACGCGAAAGCCCCTTCAAATCTGAAGCCTACAAAGACACTGTTTGTCATTAACTTTGACCCCATTAGAACAAAAGAGCACGACATTGAAAAACACTTTGAGCCCTATGGTAAGGTCACCAACGTGCGTATCAGACGCAACTTCTCATTTGTTCAGTTTGAAACACAAGAGGATGCTACAAAAGCCCTTGAAGCTACTCAAAGAAGGTAAAACAACACTTCTTCTAGTTACTTTTTCCGTCAAACTTTTAGTACATTTGCCTCTCTAAAAACATCCCCTTTTTGTTTCCTCCGCAAACTCGACAATTTTCCAGCAAAATATTGGATAGGGTTGTTTCCGTGGAGTATGCGTTGAAAGATGACGATGAAAGAGATGATCGAAATGGTGGTCGTAGCCCGAGAAGGTCTCTTAGTCCTGTGTATCGTAGGCGTCCAAGTCCAGATTATGGCAGGCGTCCAAGCCCTGGTCAGGGTAGGCGTCCAAGTCCAGATTATGGTCGTGCTCGAAGCCCAGAATACGACAGATACAAAGGTCCAGCAGCTTATGAAAGACGGAGGAGTCCAGATTATGGACGAAGGAGTTCTGATTATGGTAGACAAAGGAGCCCTGGTTATGACCGATACAGAAGGTAAAAAAAAAAAAAAATGGCTTTGGCTTCTTACTGTTCACTCAAGTCCCATTGATGTTCTTATGTAATGTTTTTGATTGAACAGTCGTTCTCCAGTCCCAAGAGGAAGACCTTGATCAATGCAGAAAACAGAAGCTTCGAAGAATGAGTTTTTCGAAAGGGTAAATTTATCTATGTTGTGTTTTGAGTTAGAAGATGCTTTTTGATGTTTTGAAATCCTTGTTAACTTTGGGATCTTAGACTTTTATCTTAAAATCAGTAGAAACTTCATAATGGCGCTTTGTCACGATCTGTTACTTGGTTCATATACTCTTTTCATTCGTCATTAATTATTCTAAATCCCATATAAATTAACAATGACACAAGTTTGCCAGAAAATAAATCATTTTGATGCAACTTTTTTCCTATATGACAACGGAAGGAAATTGTCACTTTGTTT",
                p1 = "AATGTACCCATGATGGTATGACGGGCTTTTTTAAGTCAACCAGGCTCTGCTTTTCTTCGATGAGCGTAGTAGTCGTCGTCGTCGTCTAGGGTTTGTTTTTCGTTTCTTCTCCGATTGTTCAGAGGAATTGCGAATTAAGATAAAGATGAGGCCAGTGTTCGTCGGCAATTTCGAGTATGAAACTCGCCAGTCGGATCTGGAACGGTTGTTCGACAAGTATGGGAGAGTCGACCGAGTGGACATGAAATCTGGATATGCTTTTGTGTACTTTGAGGATGAACGTGATGCTGAAGACGCTATTCGCAAACTCGACAATTTTCCTTTTGGATATGAGAAACGCAGGTTATCAGTTGAATGGGCAAAGGGTGAACGTGGCAGGCCTCGTGGTGACGCGAAAGCCCCTTCAAATCTGAAGCCTACAAAGACACTGTTTGTCATTAACTTTGACCCCATTAGAACAAAAGAGCACGACATTGAAAAACACTTTGAGCCCTATGGTAAGGTCACCAACGTGCGTATCAGACGCAACTTCTCATTTGTTCAGTTTGAAACACAAGAGGATGCTACAAAAGCCCTTGAAGCTACTCAAAGAAGCAAAATATTGGATAGGGTTGTTTCCGTGGAGTATGCGTTGAAAGATGACGATGAAAGAGATGATCGAAATGGTGGTCGTAGCCCGAGAAGGTCTCTTAGTCCTGTGTATCGTAGGCGTCCAAGTCCAGATTATGGCAGGCGTCCAAGCCCTGGTCAGGGTAGGCGTCCAAGTCCAGATTATGGTCGTGCTCGAAGCCCAGAATACGACAGATACAAAGGTCCAGCAGCTTATGAAAGACGGAGGAGTCCAGATTATGGACGAAGGAGTTCTGATTATGGTAGACAAAGGAGCCCTGGTTATGACCGATACAGAAGTCGTTCTCCAGTCCCAAGAGGAAGACCTTGATCAATGCAGAAAACAGAAGCTTCGAAGAATGAGTTTTTCGAAAGGGTAAATTTATCTATGTTGTGTTTTGAGTTAGAAGATGCTTTTTGATGTTTTGAAATCCTTGTTAACTTTGGGATCTTAGACTTTTATCTTAAAATCAGTAGAAACTTCATAATGGCGCTTTGTCACGATCTGTTACTTGGTTCATATACTCTTTTCATTCGTCATTAATTATTCTAAATCCCATATAAATTAACAATGACACAAGTTTGCCAGAAA";

            console.log(r, rev(com(r)));

            // Find all forward and reverse matches individually
            // and get all combinations of the two

            function getAllIndexes(arr, val) {
                var indexes = [], i = -1;
                while ((i = arr.indexOf(val, i+1)) != -1){
                    indexes.push(i);
                }
                return indexes;
            }
            var fwd_idx = getAllIndexes(genomic, f),
                rev_idx = getAllIndexes(genomic, revcom(r));
            console.log(fwd_idx, rev_idx);

            function myOwnDamnLookAhead(DNA, fwd_primer, rev_primer) {
                var indexes = [],
                    fwd_idx = 0,
                    i = 0;
                while (i < DNA.length) {
                    if (DNA.indexOf(fwd_primer, i) !== -1) {
                        fwd_idx = DNA.indexOf(fwd_primer, i);
                        i = fwd_idx;
                        if (DNA.indexOf(rev_primer, i) !== -1) {
                            console.log(fwd_idx, DNA.indexOf(rev_primer, i));
                        }
                    }
                    i++;
                }
                return indexes;
            }

            myOwnDamnLookAhead(genomic, f, revcom(r));

            /*
            var rx = new RegExp("("+f+".*"+revcom(r)+")", "g"),
                matches = new Array();
            while ((match = rx.exec(genomic)) !== null) {
                console.log(match);
            }*/

        </script>
    </body>
</html>