# Boxify
#### Transcript drawing made easy!
Boxify is a webtool which makes it easy to generate and customize publication-ready isoform models for Arabidopsis thaliana and can be an aid in the design and visualization of PCR primer pairs. 

### Overview
The Boxify website can be roughly divided into three sections: 1) The search field, 2) the drawing area, and 3) the settings/customization panel.

![Boxify overview](/assets/img/help.png)

#### Search (1)
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
All changes made using the various settings / customization options in this section will immediately take effect and alter the drawn transcripts.  

##### Transcript visibility / ordering (3a)
The visibility of the transcripts can be toggled in this section. By default all transcripts are displayed and at least one transcript (the last remaining visible transcript) is displayed at all times. Additionally, the order of the transcripts can be changed by dragging and dropping the transcript identifiers. 

##### Model sizing (3b)
Simply adjust the size of the isoform models by dragging the slider.

##### Display toggles (3c)
Options to hide/show the CDS regions and the sequence display. By default both are shown. __Note__: The sequence display is __not__ included in the exported image.

##### Color settings (3d)
Adjust the fill and stroke colors of exonic and coding regions. The color of the introns (the connecting lines) is based on the transcript stroke property. 

##### Reset (3e)
Reset all settings (with the exception of the transcript order) to their defaults. 

##### Downloads (3f)
Contains download buttons for saving a PNG or SVG (vector) image of the currently displayed models. If any primers were given, an additional button will be available to download the PCR results.

##### Themes (3g)
Two basic themes are available for Boxify: a light and a dark theme. The theme is automatically switched based on the time of day, with the light theme being the default from 8AM to 6PM and the dark theme for the time between 6PM and 8AM. The theme can of course always be manually changed by the user.

### Authors
Boxify was created by Peter Venhuizen and vigorously tested by Barbara Niemeth, Ezequiel Petrillo, Maria Kalyna, and Stefan Riegler, without whom this tool would never have existed!

### Issues
Please report all bugs and issues using the GitHub [issue tracker](https://github.com/KalynaLab/boxify/issues).
