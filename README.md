# Boxify
#### Transcript drawing made easy!
Boxify is a webtool which makes it easy to generate and customize publication-ready isoform models for Arabidopsis thaliana and can be an aid in the design and visualization of PCR primer pairs. 

### Overview
The Boxify website can be roughly divided into three sections: 1) The search field, 2) the drawing area, and 3) the settings/customization panel.

![Boxify overview](/assets/img/help.png)

#### Search field (1)
Basic search field. The first five matching search suggestions are given as soon as three or more character are entered. Simply press `ENTER` or click one of the suggested isoform names to start.

#### Drawing area (2)
##### Isoform models (2a)
The isoform models are dynamically drawn on a HTML Canvas element and displayed here. Users can right click on the image to either save or copy the image. The image can also be saved using one of the download buttons (see 3f).

##### Isoform sequences (2b)
Underneath the isoform models, the color-coded genomic sequence is displayed. The red rectangle scrolls along when the sequence element is scrolled and the visible nucleotides approximately correspond to the current location of said box. Clicking on the caron to the left of the genomic sequence, will display the per isoform formatted sequences. For these isoform specific sequences, the sections corresponding to exonic regions are displayed in UPPERCASE whereas the intronic sections are in lowercase. The ordering of the sequences is identical to display order (see 3a).

##### Primer search (2c)
Boxify enables the user to submit (theoretically infinite) primers to search against the currently loaded models. The user needs to submit a forward and reverse primer and if a product is found on the genomic sequence or on any of the isoforms, the product sequences and sizes are returned. Additionally, the primer locations will be added to isoform drawing, indicating the location of said primers. Each added pair of primers gets a random color assigned, making it easy to distinguish between different primer pairs. The primer search results can downloaded by clicking the "PCR result" button (not displayed in the above image) in the Downloads section. 

__Note__: The primer "boxes" are scaled based on their sequence length and thus, for very large isoforms, the boxes might just be 1 or 2 pixels in size. 

#### Settings/Customization (3)
##### Transcript visibility / ordering (3a)
##### Model sizing (3b)
##### Display toggles (3c)
##### Color settings (3d)
##### Reset (3e)
##### Downloads (3f)
##### Themes (3g)

### Authors
Boxify was created by Peter Venhuizen and vigorously tested by Barbara Niemeth, Ezequiel Petrillo, Maria Kalyna, and Stefan Riegler, without whom this tool would never have existed!

### Issues
Please report all bugs and issues using the GitHub [issue tracker](https://github.com/KalynaLab/boxify/issues).
