<?php
require '../app/Mage.php';
Mage::app();
$pid = Mage::app()->getRequest()->getParam('pid');
$product = Mage::getModel('catalog/product')->load($pid);
$price = ($product->getSpecialPrice() && $product->getSpecialPrice() < $product->getPrice())?$product->getSpecialPrice():$product->getPrice();
$product->load('media_gallery');
$gallery = $product->getMediaGallery();
$helper = Mage::Helper('wizard');
?>
<div class="custom-popup">
        <div class="popup-slider">
            <div class="popup-main-image">

                <?php if(Mage::helper('wizard')->checkRemoteFile($product->getVideo())){ ?>
                    <div class="item">
                        <div class="slick-video slick-video-2 video-wrapper">
                            <video id="videoId" autoplay="autoplay" loop="loop" onstart="this.play();" onended="this.play();" autobuffer>
                                <source src="<?php echo $product->getVideo();?>" type=video/mp4>
                            </video>
                        </div>
                    </div>
                <?php } ?>
                <?php if(count($gallery['images'])>0){ ?>
                    <?php foreach ($gallery['images'] as $img) : ?>
                        <div class="item"><img src="<?php echo Mage::helper('catalog/image')->init($product, 'thumbnail', $img['file'])->backgroundColor(255,255,255)->resize(170,170);?>" alt="<?php echo $product->getName();?>" /></div>
                    <?php endforeach; ?>
                <?php }else{ ?>
                    <div class="item"><img src="<?php echo Mage::getSingleton('catalog/product_media_config')->getBaseMediaUrl(). '/placeholder/' .Mage::getStoreConfig("catalog/placeholder/small_image_placeholder"); ?>" alt="" /></div>
                <?php } ?>
            </div>
            <div class="links">
                <a class="link wishlist-link" href="#"><span>Wish It</span></a>
                <a class="link" href="javascript:void(0);">
                    <div class="sharethis-inline-share-buttons"></div>
                    <script type="text/javascript" src="//platform-api.sharethis.com/js/sharethis.js#property=5a423282c4203c00110b8eca&product=unknown" async="async"></script>
                </a>
            </div>
        </div>
        <div class="popup-details">
            <input type="hidden" value="<?php echo $product->getId(); ?>">
            <h2><?php echo $product->getDescription();?></h2>
            <p><?php echo Mage::helper('catalog')->__('Cut: ');?><strong><?php echo $helper->getAttributeValue('stone_cut',$product->getStoneCut()); ?></strong> | <?php echo Mage::helper('catalog')->__('Carat: ');?> <strong><?php echo number_format($product->getWeight(),2); ?></strong> | <?php echo Mage::helper('catalog')->__('Color: ');?> <strong><?php echo $helper->getAttributeValue('stone_color',$product->getStoneColor()); ?></strong> | <?php echo Mage::helper('catalog')->__('Clarity: ');?> <strong><?php echo $helper->getAttributeValue('stone_quality',$product->getStoneQuality()); ?></strong></p>
            <div class="price-box"><span class="price"><?php echo Mage::helper('core')->currency($price, true, false);?></span></div>
            <div class="links"> <a href="javascript:loadAjaxList(<?php echo $product->getId(); ?>);" class="link">View More</a> <a href="javascript:loadAjaxList(<?php echo $product->getId(); ?>);" class="link">I’ll Take This</a> </div>
            <?php //if(count($gallery['images'])>0){ ?>
                <div class="popup-thumb-image">
                     <?php if(Mage::helper('wizard')->checkRemoteFile($product->getVideo())){ ?>
                        <div class="item"><img src="<?php echo Mage::getBaseURL().'skin/frontend/faaya/faaya/css/images/video-icons.png'; ?>" alt="" /></div>
                    <?php } ?>
                    <?php foreach ($gallery['images'] as $img) : ?>
                        <div class="item">
                            <img src="<?php echo Mage::helper('catalog/image')->init($product, 'thumbnail', $img['file'])->backgroundColor(255,255,255)->resize(60,60);?>" alt="<?php echo $product->getName();?>" />
                        </div>
                    <?php endforeach; ?>

                </div>
            <?php //} ?>
        </div>
        <button data-fancybox-close="" class="fancybox-close-small" title="Close"></button>
</div>
<script type="text/javascript">
jQuery(document).ready(function(){
    popupSlider();
})
</script>