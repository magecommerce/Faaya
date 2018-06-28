<?php
require '../app/Mage.php';
Mage::app();
$pid = Mage::app()->getRequest()->getParam('pid');
$oid = Mage::app()->getRequest()->getParam('oid');
if($pid > 0){
$product = Mage::getModel('catalog/product')->load($pid);
$oldProduct = Mage::getModel('catalog/product')->load($oid);
$price = ($product->getSpecialPrice() && $product->getSpecialPrice() < $product->getPrice())?$product->getSpecialPrice():$product->getPrice();
$oldPrice = ($oldProduct->getSpecialPrice() && $oldProduct->getSpecialPrice() < $oldProduct->getPrice())?$oldProduct->getSpecialPrice():$oldProduct->getPrice();

$price = $price-$oldPrice;
$product->load('media_gallery');
$gallery = $product->getMediaGallery();
$helper = Mage::Helper('wizard');
?>

<div class="custom-popup">
    <span class="error-msg"><?php echo Mage::helper('catalog')->__('Diamond with this style is not available so we are adding another one');?></span>
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
        </div>
        <div class="popup-details">
            <input type="hidden" value="<?php echo $product->getId(); ?>">
            <h2><?php echo $product->getDescription();?></h2>
            <p><?php echo Mage::helper('catalog')->__('Cut: ');?><strong><?php echo $helper->getAttributeValue('stone_cut',$oldProduct->getStoneCut()); ?></strong> | <?php echo Mage::helper('catalog')->__('Carat: ');?> <strong><?php echo number_format($oldProduct->getWeight(),2); ?></strong> | <?php echo Mage::helper('catalog')->__('Color: ');?> <strong><?php echo $helper->getAttributeValue('stone_color',$oldProduct->getStoneColor()); ?></strong> | <?php echo Mage::helper('catalog')->__('Clarity: ');?> <strong><?php echo $helper->getAttributeValue('stone_quality',$oldProduct->getStoneQuality()); ?></strong></p>
            <p><?php echo Mage::helper('catalog')->__('Replace with');?></p>
            <p><?php echo Mage::helper('catalog')->__('Cut: ');?><strong><?php echo $helper->getAttributeValue('stone_cut',$product->getStoneCut()); ?></strong> | <?php echo Mage::helper('catalog')->__('Carat: ');?> <strong><?php echo number_format($product->getWeight(),2); ?></strong> | <?php echo Mage::helper('catalog')->__('Color: ');?> <strong><?php echo $helper->getAttributeValue('stone_color',$product->getStoneColor()); ?></strong> | <?php echo Mage::helper('catalog')->__('Clarity: ');?> <strong><?php echo $helper->getAttributeValue('stone_quality',$product->getStoneQuality()); ?></strong></p>
            <div class="price-box"><span class="price"><?php echo Mage::helper('catalog')->__('Price difference: ');?><?php echo Mage::helper('core')->currency($price, true, false);?></span></div>
            <div class="links"><a href="javascript:addcart(<?php echo $product->getId(); ?>);" class="link">I’ll Take This</a> </div>
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
</div>
<script type="text/javascript">
jQuery(document).ready(function(){
    popupSlider();
})
</script>
<?php }else{ ?>
    <div class="custom-alert">
        <span class="error-msg"><?php echo Mage::helper('catalog')->__('This diamond is not available for this setting');?></span>
    </div>
<?php } ?>