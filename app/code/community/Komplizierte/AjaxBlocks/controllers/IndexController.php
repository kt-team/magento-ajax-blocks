<?php

class Komplizierte_AjaxBlocks_IndexController extends Mage_Core_Controller_Front_Action {

    public function blockReloadAction() {
        $handle = $this->getRequest()->getPost('handle');
        $blockName = $this->getRequest()->getPost('blockName');
        $productId = $this->getRequest()->getPost('productId');

        if (isset($productId)) {
            $product = Mage::helper('catalog/product')->initProduct($productId, $this);
            $this->getLayout()->getUpdate()->addHandle(array(
                'default',
                'catalog_product_view',
                'PRODUCT_TYPE_' . $product->getTypeId(),
                'PRODUCT_' . $product->getId()
            ));

            $this->loadLayout();
        } else {
            $this->loadLayout($handle);
        }

        if ($block = $this->getLayout()->getBlock($blockName)) {
            $this->getResponse()->setBody($block->toHtml());
        }else{
            $this->getResponse()->setBody('');
        }
        return;
    }

    // Method reload array blocks
    // Example on jQuery
    // $j.post('http://tvoe.dev/ajaxblocks/index/blocksreload',
    // {blocks: [{handle: 'default', name: 'login.link'}, {handle: 'default', name: 'menu_bottom'}]})
    public function blocksReloadAction() {
        $blocks = $this->getRequest()->getPost('blocks');

        $layout = Mage::app()->getLayout();

        foreach($blocks as $block) {
            $layout->getUpdate()->addHandle($block['handle']);
        }

        $layout->getUpdate()->load();
        $layout->generateXml()->generateBlocks();

        $responseBlocks = array();
        foreach($blocks as $block) {
            $layoutBlock = $layout->getBlock($block['name']);

            $responseBlockName = str_replace('.','_',$block['name']);
            $responseBlocks[$responseBlockName] = $layoutBlock ? $layoutBlock->toHtml() : '';
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($responseBlocks));
        return;
    }

    public function lastMessageAction() {
        $session = Mage::getSingleton('customer/session');

        $response = array();
        if ($session->getMessages(true)->getLastAddedMessage()) {
            $response['message'] = $session->getMessages(true)->getLastAddedMessage()->getText();
            $response['status'] = $session->getMessages(true)->getLastAddedMessage()->getType();
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        return;
    }
}
