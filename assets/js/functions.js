function rev(seq) {
	// Reverse sequence
	var r = new Array();
    seq = seq.replace(/ /g, '');
	for (var i = 0, len = seq.length; i <= len; i++) {
		r.push(seq.charAt(len - i));
	}
	return r.join('');
}

function com(seq) {
	// Create the complementary sequence,
	// conserve upper and lowercase letters
	var rc_seq = new Array();
	for (var i = 0; i < seq.length; i++) {
		switch(seq[i]) {
			case 'a': rc_seq[i] = 't'; break;
			case 'A': rc_seq[i] = 'T'; break;
			case 'u': rc_seq[i] = 'a'; break;
			case 'U': rc_seq[i] = 'A'; break;
			case 't': rc_seq[i] = 'a'; break;
			case 'T': rc_seq[i] = 'A'; break;
			case 'c': rc_seq[i] = 'g'; break;
			case 'C': rc_seq[i] = 'G'; break;
			case 'g': rc_seq[i] = 'c'; break;
			case 'G': rc_seq[i] = 'C'; break;
            case 'M': rc_seq[i] = 'K'; break;
            case 'm': rc_seq[i] = 'k'; break;
            case 'R': rc_seq[i] = 'Y'; break;
            case 'r': rc_seq[i] = 'y'; break;
            case 'W': rc_seq[i] = 'W'; break;
            case 'w': rc_seq[i] = 'w'; break;
            case 'S': rc_seq[i] = 'S'; break;
            case 's': rc_seq[i] = 's'; break;
            case 'Y': rc_seq[i] = 'R'; break;
            case 'y': rc_seq[i] = 'r'; break;
            case 'K': rc_seq[i] = 'M'; break;
            case 'k': rc_seq[i] = 'm'; break;
            case 'V': rc_seq[i] = 'B'; break;
            case 'v': rc_seq[i] = 'b'; break;
            case 'H': rc_seq[i] = 'D'; break;
            case 'D': rc_seq[i] = 'H'; break;
            case 'B': rc_seq[i] = 'V'; break;
            case 'N': rc_seq[i] = 'N'; break;
			case '-': rc_seq[i] = '-'; break;
			default: rc_seq[i] = 'X';
 		}
	}
	return rc_seq.join('');
}

function revcom(seq) {
	// Return the reverse complement 

	return com(rev(seq));
}

function getFrameSeq(seq, frame) {
	if (frame == '+2') { seq = seq.slice(1); }
	else if (frame == '+3') { seq = seq.slice(2); }
	else if (frame == '-1') { seq = revcom(seq); }
	else if (frame == '-2') { seq = revcom(seq).slice(1); }
	else if (frame == '-3') { seq = revcom(seq).slice(2); }	
	
	return seq;
}

function translate(seq, one_letter) {
	
	var aa = new Array();
	
	// Replace U's and make uppercase
	seq = seq.toUpperCase();
	seq = seq.replace(/U/g, 'T');
	
	// Iterate over the codons in the sequence
	for (var i=0; i < seq.length; i+=3) {
		var codon = seq.slice(i, i+3);
		if (codon.length == 3) {
			if (['GCT', 'GCC', 'GCA', 'GCG'].indexOf(codon) !== -1) { one_letter ? aa.push('A') : aa.push('Ala'); }
			else if (['CGT', 'CGC', 'CGA', 'CGG', 'AGA', 'AGG'].indexOf(codon) !== -1) { one_letter ? aa.push('R') : aa.push('Arg'); }
			else if (['AAT', 'AAC'].indexOf(codon) !== -1) { one_letter ? aa.push('N') : aa.push('Asp'); }
			else if (['GAT', 'GAC'].indexOf(codon) !== -1) { one_letter ? aa.push('D') : aa.push('Asp'); }
			else if (['TGT', 'TGC'].indexOf(codon) !== -1) { one_letter ? aa.push('C') : aa.push('Cys'); }
			else if (['CAA', 'CAG'].indexOf(codon) !== -1) { one_letter ? aa.push('Q') : aa.push('Gln'); }
			else if (['GAA', 'GAG'].indexOf(codon) !== -1) { one_letter ? aa.push('E') : aa.push('Glu'); }
			else if (['GGT', 'GGC', 'GGA', 'GGG'].indexOf(codon) !== -1) { one_letter ? aa.push('G') : aa.push('Gly'); }
			else if (['CAT', 'CAC'].indexOf(codon) !== -1) { one_letter ? aa.push('H') : aa.push('His'); }
			else if (['ATT', 'ATC', 'ATA'].indexOf(codon) !== -1) { one_letter ? aa.push('I') : aa.push('Ile'); }
			else if (['TTA', 'TTG', 'CTT', 'CTC', 'CTA', 'CTG'].indexOf(codon) !== -1) { one_letter ? aa.push('L') : aa.push('Leu'); }
			else if (['AAA', 'AAG'].indexOf(codon) !== -1) { one_letter ? aa.push('K') : aa.push('Lys'); }
			else if (['ATG'].indexOf(codon) !== -1) { one_letter ? aa.push('M') : aa.push('Met'); }
			else if (['TTT', 'TTC'].indexOf(codon) !== -1) { one_letter ? aa.push('F') : aa.push('Phe'); }
			else if (['CCT', 'CCC', 'CCA', 'CCG'].indexOf(codon) !== -1) { one_letter ? aa.push('P') : aa.push('Pro'); }			
			else if (['TCT', 'TCC', 'TCA', 'TCG', 'AGT', 'AGC'].indexOf(codon) !== -1) { one_letter ? aa.push('S') : aa.push('Ser'); }									
			else if (['ACT', 'ACC', 'ACA', 'ACG'].indexOf(codon) !== -1) { one_letter ? aa.push('T') : aa.push('Thr'); }		
			else if (['TGG'].indexOf(codon) !== -1) { one_letter ? aa.push('W') : aa.push('Trp'); }									
			else if (['TAT', 'TAC'].indexOf(codon) !== -1) { one_letter ? aa.push('Y') : aa.push('Tyr'); }	
			else if (['GTT', 'GTC', 'GTA', 'GTG'].indexOf(codon) !== -1) { one_letter ? aa.push('V') : aa.push('Val'); }									
			else if (['TAA', 'TGA', 'TAG'].indexOf(codon) !== -1) { one_letter ? aa.push('*') : aa.push(' * '); }
			else { one_letter ? aa.push('X') : aa.push('XXX'); }
		}
	}
	
	return aa.join('');
	
}