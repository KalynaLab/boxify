#!/usr/bin/env python3

"""Create boxify database tables from a GTF file"""

__version__ = "2021.02.04-15:33"
__author__  = "Peter Venhuizen"
__email__   = "peter.venhuizen@boku.ac.at"

import os
import gzip
import argparse
import subprocess

def create_boxify_db_tables(gtf_file, genome_fasta, zip=True):
    """Create the boxify database tables from a GTF file
    
    Parameters
    ----------
    gtf_file : str
        Input GTF annotation with exon and CDS regions
    genome_fasta : str
        Genomic fasta sequence
    zip : boolean
        Indicate whether the output data tables should be gzipped or not

    Output
    ------
    cds.tsv
        TAB-separated file containing the transcript_id, start and end 
        coordinates for the CDS regions
    exons.tsv
        TAB-separated file containing the transcript_id, start and end
        coordinates for the exon regions
    genes.tsv
        TAB-separated file containing the gene_id, chromosome, start and end
        coordinates, strand, and genomic sequence of the genes
    """
    
    # Parse the exon and CDS regions per transcript from the GTF file
    gtf, g2t = {}, {}
    for line in open(gtf_file):
        if not line.startswith('#'):
            c, source, feature, start, end, score, strand, frame, attributes = line.rstrip().split('\t')
            if feature in ["CDS", "exon"]:
               
                # Parse attributes key, value pairs
                attr = {}
                for a in attributes.split(';'):
                    if len(a):
                        attr_name, attr_value = list(filter(None, a.split(' ')))
                        attr[attr_name.strip()] = attr_value.replace('\"', '')

                # Store exon and CDS coordinates
                try: gtf[attr["transcript_id"]][feature].append([ int(start), int(end) ])
                except KeyError: 
                    gtf[attr["transcript_id"]] = { "exon": [], "CDS": [] }
                    gtf[attr["transcript_id"]][feature].append([ int(start), int(end) ])

                # Store transcript identifiers per gene
                try: g2t[attr["gene_id"]]["transcripts"].add(attr["transcript_id"])
                except KeyError: g2t[attr["gene_id"]] = { "chr": c, "strand": strand, "transcripts": set([ attr["transcript_id"] ]) }

    # Output the cds.tsv and exons.tsv files and get the gene coordinates
    cds_out = gzip.open("cds.tsv.gz", 'wt') if zip else open("cds.tsv", 'w')
    exon_out = gzip.open("exons.tsv.gz", 'wt') if zip else open("exons.tsv", 'w')

    genes = {}
    gene_bed = open("gene_coord.bed", 'w')
    for g_id in sorted(g2t):
        
        # Write CDS and exons coordinates
        for t_id in sorted(g2t[g_id]["transcripts"]):
            cds_out.write( '\n'.join([ "{}\t{}\t{}".format(t_id, x[0], x[1]) for x in gtf[t_id]["CDS"] ]) + '\n' )
            exon_out.write( '\n'.join([ "{}\t{}\t{}".format(t_id, x[0], x[1]) for x in gtf[t_id]["exon"] ]) + '\n')

        # Get the start and end coordinates of each gene and write to BED file
        start = min([ min(x) for t_id in g2t[g_id]["transcripts"] for x in gtf[t_id]["exon"] ])
        end = max([ max(x) for t_id in g2t[g_id]["transcripts"] for x in gtf[t_id]["exon"] ])
        g2t[g_id]["start"] = start
        g2t[g_id]["end"] = end
        gene_bed.write("{}\t{}\t{}\t{}\t1000\t{}\n".format(g2t[g_id]["chr"], start-1, end, g_id, g2t[g_id]["strand"]))

    gene_bed.close(), cds_out.close(), exon_out.close()

    # Get the genomic sequences of the genes
    subprocess.call("bedtools getfasta -s -name -tab -fi {} -bed gene_coord.bed > gene_seq.tab".format(genome_fasta), shell=True)

    genes_out = gzip.open("genes.tsv.gz", 'wt') if zip else open("genes.tsv", 'w')
    for line in open("gene_seq.tab"):
        g_id, seq = line.rstrip().split('\t')
        g_id = g_id[:-3]
        genes_out.write( "{}\t{}\t{}\t{}\t{}\t{}\n".format(
                g_id, 
                g2t[g_id]["chr"],
                g2t[g_id]["start"],
                g2t[g_id]["end"],
                g2t[g_id]["strand"],
                seq
            )
        )
    genes_out.close()

    # Clean-up
    subprocess.call("rm gene_coord.bed gene_seq.tab", shell=True)

def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('-g', '--gtf', required=True, help="Annotation GTF file.")
    parser.add_argument('-f', '--fasta', required=True, help="Genomic sequences in fasta format")
    parser.add_argument('--zip', action='store_true', help="Gunzip the output files")
    args = parser.parse_args()

    create_boxify_db_tables(args.gtf, args.fasta, args.zip)

if __name__ == '__main__':
    main()