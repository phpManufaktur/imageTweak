### imageTweak

imageTweak optimize images on the fly while delivering pages of your website to the browser.

Fast and efficient Admin-Tool for the Content Management System [WebsiteBaker] [1] or [LEPTON CMS] [2].

As webmaster you know this problem: The user of the CMS copy and paste images directly from there digital cameras and just resize them. These images will be very great, needs a long time to be loaded and are out of focus. imageTweak is the solution, it will automatically resize, optimize and rewrite the images to a new one which will be loaded very fast and looks better. imageTweak will only care about resized images, so if an image is already optimized it will not touched. 

#### Requirements

* minimum PHP 5.2.x
* using [WebsiteBaker] [1] _or_ using [LEPTON CMS] [2]
* [dbConnect_LE] [7] must be installed
* [Dwoo] [6] must be installed 
* [kitTools] [8] must be installed
* [wblib] [9] must be installed
* [LibraryAdmin] [10] must be installed
* [lib_jquery] [11] must be installed

#### Installation

* download the actual [imageTweak_x.xx.zip] [3] installation archive
* in CMS backend select the file from "Add-ons" -> "Modules" -> "Install module"

#### First Steps

imageTweak use the **output filter** of the CMS to process the images before the pages are displayed. In [LEPTON CMS] [2] imageTweak only needs to _register_ his own filter, at [WebsiteBaker] [1] the installation must _patch_ the output filter _physically_. If the patching of the output filter fails, please contact the [Addons Support Group] [5] to get further informations and help.

After the installation imageTweak will still work by itself, there is nothing else to do. In the backend at "Admin-Tools" --> "imageTweak" you will find the settings and a protocol for the activities of imageTweak.

imageTweak gives you some additional features, i.e. the usage of the [FancyBox] [12]. imageTweak has prepared this for you. In the [LibraryAdmin] [10] you will find the preset `it_gallery.jquery`, just insert the droplet code for this preset at the pages where you want to use the FancyBox:

    [[LibInclude?lib=lib_jquery&preset=it_gallery&module=image_tweak]] 

and add the class `tweak-fancybox` to images you want to use with. 

Please visit the [phpManufaktur] [4] to get more informations about **imageTweak** and join the [Addons Support Group] [5] for technical support.

[1]: http://websitebaker2.org "WebsiteBaker Content Management System"
[2]: http://lepton-cms.org "LEPTON CMS"
[3]: https://github.com/phpManufaktur/imageTweak/downloads
[4]: https://phpmanufaktur.de
[5]: https://phpmanufaktur.de/support
[6]: https://github.com/phpManufaktur/Dwoo/downloads
[7]: https://github.com/phpManufaktur/dbConnect_LE/downloads
[8]: https://github.com/phpManufaktur/kitTools/downloads
[9]: https://github.com/webbird/wblib/downloads
[10]: http://jquery.lepton-cms.org/modules/download_gallery/dlc.php?file=75&id=1318585713
[11]: http://jquery.lepton-cms.org/modules/download_gallery/dlc.php?file=76&id=1320743410
[12]: http://fancybox.net/
