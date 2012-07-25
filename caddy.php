<?php
//  ------------------------------------------------------------------------ //
//                      BOOKSHOP - MODULE FOR XOOPS 2                		 //
//                  Copyright (c) 2007, 2008 Instant Zero                    //
//                     <http://www.instant-zero.com/>                        //
// ------------------------------------------------------------------------- //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
/**
 * Affichage et gestion du caddy
 */
include 'header.php';
$GLOBALS['current_category'] = -1;
$xoopsOption['template_main'] = 'bookshop_caddy.html';
include_once XOOPS_ROOT_PATH.'/header.php';

$xoopsTpl->assign('mod_pref', $mod_pref);	// Pr�f�rences du module

$op = 'default';
if(isset($_POST['op'])) {
	$op = $_POST['op'];
} elseif(isset($_GET['op'])) {
	$op = $_GET['op'];
}

$book_id = 0;
if(isset($_POST['book_id'])) {
	$book_id = $_POST['book_id'];
} elseif(isset($_GET['book_id'])) {
	$book_id = $_GET['book_id'];
}

$xoopsTpl->assign('op', $op);
$xoopsTpl->assign('confEmpty', bookshop_JavascriptLinkConfirm(_BOOKSHOP_EMPTY_CART_SURE,true));
$xoopsTpl->assign('confirm_delete_book', bookshop_JavascriptLinkConfirm(_BOOKSHOP_EMPTY_ITEM_SURE, false));


// ********************************************************************************************************************
// Liste le contenu du caddy
// ********************************************************************************************************************
function listCaddie()
{
	global $xoopsTpl, $h_bookshop_caddy;
	$cartForTemplate = array();
	$emptyCart = false;
	$shippingAmount = 0;
	$commandAmount = 0;
	$vatAmount = 0;
	$goOn = '';
	$commandAmountTTC = 0;
	$discountsDescription = array();

	$h_bookshop_caddy->computeCart($cartForTemplate, $emptyCart, $shippingAmount, $commandAmount, $vatAmount, $goOn, $commandAmountTTC, $discountsDescription);
	$dec = bookshop_getmoduleoption('decimals_count');

	$xoopsTpl->assign('emptyCart', $emptyCart);											// Caddy Vide ?
	$xoopsTpl->assign('caddieProducts', $cartForTemplate);								// Produits dans le caddy
	$xoopsTpl->assign('shippingAmount', sprintf("%0.".$dec.'f', $shippingAmount));		// Montant des frais de port
	$xoopsTpl->assign('commandAmount', sprintf("%0.".$dec.'f', $commandAmount));		// Montant HT de la commande
	$xoopsTpl->assign('vatAmount', sprintf("%0.".$dec.'f', $vatAmount));				// Montant de la TVA
	$xoopsTpl->assign('goOn', $goOn);													// Adresse � utiliser pour continuer ses achats
	$xoopsTpl->assign('commandAmountTTC', sprintf("%0.".$dec.'f', $commandAmountTTC));	// Montant TTC de la commande
	$xoopsTpl->assign('discountsDescription', $discountsDescription);					// Liste des r�ductions accord�es
}

// ********************************************************************************************************************
// ********************************************************************************************************************
// ********************************************************************************************************************
switch ($op) {
	// ****************************************************************************************************************
	case 'update':	// Recalcul des quantit�s
	// ****************************************************************************************************************
		$h_bookshop_caddy->updateQuantites();
		listCaddie();
		break;

	// ****************************************************************************************************************
	case 'delete':	// Suppression d'un �l�ment
	// ****************************************************************************************************************
		$book_id--;
		$h_bookshop_caddy->deleteProduct($book_id);
		listCaddie();
		break;

	// ****************************************************************************************************************
	case 'addbook':	// Ajout d'un livre
	// ****************************************************************************************************************
		if($book_id == 0) {
			bookshop_redirect(_BOOKSHOP_ERROR9, 'index.php', 4);
		}

		$book = null;
		$book = $h_bookshop_books->get($book_id);
		if(!is_object($book)) {
			bookshop_redirect(_BOOKSHOP_ERROR9, 'index.php', 4);
		}
		if($book->getVar('book_online') == 0) {
			bookshop_redirect(_BOOKSHOP_ERROR2, 'index.php', 4);
		}

		if($book->getVar('book_stock') - 1 >= 0) {
			$h_bookshop_caddy->addProduct($book_id, 1);
			$url = BOOKSHOP_URL.'caddy.php';
			header("Location: $url");
		} else {
			bookshop_redirect(_BOOKSHOP_PROBLEM_QTY,'index.php', 5);	// Plus de stock !
		}
		listCaddie();
		break;

	// ****************************************************************************************************************
	case 'empty':	// Suppression du contenu du caddy
	// ****************************************************************************************************************
		$h_bookshop_caddy->emptyCart();
		listCaddie();
		break;

	// ****************************************************************************************************************
	case 'default':	// Action par d�faut
	// ****************************************************************************************************************
		listCaddie();
		break;
}

bookshop_setCSS();
if (file_exists( BOOKSHOP_PATH.'language/'.$xoopsConfig['language'].'/modinfo.php')) {
	include_once  BOOKSHOP_PATH.'language/'.$xoopsConfig['language'].'/modinfo.php';
} else {
	include_once  BOOKSHOP_PATH.'language/english/modinfo.php';
}

$title = _MI_BOOKSHOP_SMNAME1.' - '.bookshop_get_module_name();
bookshop_set_metas($title, $title);
include_once XOOPS_ROOT_PATH.'/footer.php';
?>