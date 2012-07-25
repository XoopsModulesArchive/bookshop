<?php
/**
 * ****************************************************************************
 * bookshop - MODULE FOR XOOPS
 * Copyright (c) Herv� Thouzard of Instant Zero (http://www.instant-zero.com)
 * Created on 22 d�c. 07 at 18:48:08
 * ****************************************************************************
 */

/**
 * Affiche la liste des livres recommand�s
 */

include 'header.php';
$GLOBALS['current_category'] = -1;
$xoopsOption['template_main'] = 'bookshop_recommended.html';
include_once XOOPS_ROOT_PATH.'/header.php';
include_once BOOKSHOP_PATH.'class/registryfile.php';
include_once XOOPS_ROOT_PATH.'/class/pagenav.php';

// Initialisations
$tbl_books = $tbl_categories = $tbl_lang = $tbl_users = $tbl_tmp_user  = $tbl_tmp_categ = $tbl_tmp_lang = $tbl_tmp_vat = $tbl_vat = array();
$tbl_books_id = $tbl_auteurs = $tbl_infos_auteurs = $tbl_tmp_auteurs = array();
$tbl_tmp_related = $tbl_related = $tbl_info_related_books = array();
$tbl_related_books = array();
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$limit = bookshop_getmoduleoption('perpage');				// Nombre maximum d'�l�ments � afficher dans l'admin
$baseurl = BOOKSHOP_URL.basename(__FILE__);					// URL de ce script (sans son nom)

$registry = new bookshop_registryfile();

// Quelques options pour le template
$xoopsTpl->assign('nostock_msg', bookshop_getmoduleoption('nostock_msg'));
$xoopsTpl->assign('mod_pref', $mod_pref);	// Pr�f�rences du module
$xoopsTpl->assign('welcome_msg', nl2br($registry->getfile('bookshop_recomm.txt')));

$itemsCount = $h_bookshop_books->getRecommendedCount();
if($itemsCount > $limit) {
	$pagenav = new XoopsPageNav( $itemsCount, $limit, $start);
	$xoopsTpl->assign('pagenav', $pagenav->renderNav());
}

if($limit > 0) {
	// R�cup�ration de la liste des livres r�cents
	$tbl_books = $h_bookshop_books->getRecentRecommendedBooks($start, $limit);

	// R�cup�ration des ID uniquement n�cessaires
	foreach($tbl_books as $item) {
		$tbl_tmp_user[] = $item->getVar('book_submitter');
		$tbl_tmp_categ[] = $item->getVar('book_cid');
		$tbl_tmp_lang[] = $item->getVar('book_lang_id');
		$tbl_tmp_vat[] = $item->getVar('book_vat_id');
		$tbl_books_id[] = $item->getVar('book_id');
	}
	// D�doublonnage des tableaux
	$tbl_tmp_user = array_unique($tbl_tmp_user);
	$tbl_tmp_categ = array_unique($tbl_tmp_categ);
	$tbl_tmp_lang = array_unique($tbl_tmp_lang);
	$tbl_tmp_vat = array_unique($tbl_tmp_vat);

	sort($tbl_tmp_user);
	sort($tbl_tmp_categ);
	sort($tbl_tmp_lang);
	sort($tbl_tmp_vat);
	sort($tbl_books_id);

	// R�cup�ration de la liste des auteurs
	// On commence en cherchant la liste de tous les auteurs et traducteurs de tous les livres
	$tbl_books_auteurs = array();
	$tbl_auteurs = $h_bookshop_booksauthors->getObjects(new Criteria('ba_book_id', '('.implode(',', $tbl_books_id).')', 'IN'), true);
	if(count($tbl_auteurs) > 0 ) {
		foreach($tbl_auteurs as $item) {
			$tbl_tmp_auteurs[]  = $item->getVar('ba_auth_id');
			// Regroupement des donn�es par livre
			$tbl_books_auteurs[$item->getVar('ba_book_id')][] = $item;
		}
		$tbl_tmp_auteurs = array_unique($tbl_tmp_auteurs);
		sort($tbl_tmp_auteurs);
		// Puis on r�cup�re les informations de ces auteurs/traducteurs
		$tbl_infos_auteurs = $h_bookshop_authors->getObjects(new Criteria('auth_id', '('.implode(',', $tbl_tmp_auteurs).')', 'IN'), true);
	}

	// R�cup�ration de la liste de tous les livres relatifs
	$tbl_related = $h_bookshop_related->getObjects(new Criteria('related_book_id', '('.implode(',', $tbl_books_id).')', 'IN'), true);
	foreach($tbl_related as $item) {
		$tbl_tmp_related[] = $item->getVar('related_book_related');
		$tbl_related_books[$item->getVar('related_book_id')][] = $item;
	}
	$tbl_tmp_related = array_unique($tbl_tmp_related);
	sort($tbl_tmp_related);

	// Ensuite on r�cup�re les ID et titre des livres
	if(count($tbl_tmp_related) > 0 ) {
		$tbl_info_related_books = $h_bookshop_books->getIdTitle(new Criteria('book_id', '('.implode(',', $tbl_tmp_related).')', 'IN'));
	}

	// R�cup�ration de la liste des cat�gories
	if(count($tbl_tmp_categ) > 0 ) {
		$tbl_categories = $h_bookshop_cat->getObjects(new Criteria('cat_cid', '('.implode(',', $tbl_tmp_categ).')', 'IN'), true);
	}

	// R�cup�ration de la liste des langues
	if(count($tbl_tmp_lang) > 0 ) {
		$tbl_lang = $h_bookshop_lang->getObjects(new Criteria('lang_id', '('.implode(',', $tbl_tmp_lang).')', 'IN'), true);
	}

	// R�cup�ration de la liste des TVA
	if(count($tbl_tmp_vat) > 0 ) {
		$tbl_vat = $h_bookshop_vat->getObjects(new Criteria('vat_id', '('.implode(',', $tbl_tmp_vat).')', 'IN'), true);
	}

	// R�cup�ration de la liste des personnes qui ont publi�s ces livres r�cents
	if(count($tbl_tmp_user) >0) {
		$user_handler = $member_handler =& xoops_gethandler('user');
		$criteria = new Criteria('uid', '('.implode(',', $tbl_tmp_user).')', 'IN');
		$tbl_users = $user_handler->getObjects($criteria, true);
	}

	// Traitement des livres
	$lastTitle = '';
	foreach($tbl_books as $item) {
		$tbl_tmp = array();
		$tbl_tmp = $item->toArray();
		if(xoops_trim($lastTitle)  ==  '' ) {
			$lastTitle = $item->getVar('book_title');
		}
		$tbl_tmp['book_category'] = $tbl_categories[$item->getVar('book_cid')];
		$tbl_tmp['book_language'] = $tbl_lang[$item->getVar('book_lang_id')];
		$thisuser = $tbl_users[$item->getVar('book_submitter')];
		if(xoops_trim($thisuser->getVar('name')) != '') {
			$name = $thisuser->getVar('name');
		} else {
			$name = $thisuser->getVar('uname');
		}
		$tbl_tmp['book_submiter_name'] = $name;
		$linkeduser = '<a href="'.XOOPS_URL.'/userinfo.php?uid='.$thisuser->getVar('uid').'">'. $name.'</a>';
		$tbl_tmp['book_submiter_link'] = $name;
		$tbl_tmp['book_vat_rate'] = $tbl_vat[$item->getVar('book_vat_id')];
		$tbl_tmp['book_price_ttc'] = bookshop_getTTC($item->getVar('book_price'), $tbl_vat[$item->getVar('book_vat_id')]->getVar('vat_rate'));
		$tbl_tmp['book_discount_price_ttc'] = bookshop_getTTC($item->getVar('book_discount_price'), $tbl_vat[$item->getVar('book_vat_id')]->getVar('vat_rate'));

		// Recherche des auteurs & des traducteurs
		$tbl_join1 = $tbl_join2 = array();
		if(isset($tbl_books_auteurs[$item->getVar('book_id')])) {
			$tbl_tmp2 = $tbl_books_auteurs[$item->getVar('book_id')];	// Renvoie la liste de tous les auteurs/traducteurs d'un livre
		} else {
			$tbl_tmp2 = array();
		}
		$tbl_livre_auteurs = $tbl_livre_traducteurs = array();
		foreach($tbl_tmp2 as $oneauthor) {
			$auteur = $tbl_infos_auteurs[$oneauthor->getVar('ba_auth_id')];
			if($oneauthor->getVar('ba_type') == 1) {
				$tbl_livre_auteurs[] = $auteur->toArray();
				$tbl_join1[] = $auteur->getVar('auth_firstname').' '.$auteur->getVar('auth_name');
			} else {
				$tbl_livre_traducteurs[] = $auteur->toArray();
				$tbl_join2[] = $auteur->getVar('auth_firstname').' '.$auteur->getVar('auth_name');
			}
		}
		if(count($tbl_join1) > 0) {
			$tbl_tmp['book_joined_authors'] = implode(', ', $tbl_join1);
		}
		if(count($tbl_join2) > 0) {
			$tbl_tmp['book_joined_translators'] = implode(', ', $tbl_join2);
		}
		$tbl_tmp['book_authors'] = $tbl_livre_auteurs;
		$tbl_tmp['book_translators'] = $tbl_livre_traducteurs;

		// Recherche des livres relatifs, s'il y en a !
		$tbl_related = $tbl_tmp2 = array();
		if(isset($tbl_related_books[$item->getVar('book_id')])) {
			$tbl_tmp2 = $tbl_related_books[$item->getVar('book_id')];	// Contient la liste des livres relatifs � CE livre
			foreach($tbl_tmp2 as $onerelated) {
				$book_id = $onerelated->getVar('related_book_id');
				if(isset($tbl_info_related_books[$book_id])) {
					$tbl_related[] = array('related_book_id' => $book_id, 'related_book_title' => $tbl_info_related_books[$book_id]);
				}
			}
		}
		$tbl_tmp['book_related_books'] = $tbl_related;
		// Et on place le tout dans le template
		$xoopsTpl->append('books', $tbl_tmp);
	}
}

bookshop_setCSS();
bookshop_set_metas(_BOOKSHOP_RECOMMENDED.' - '.bookshop_get_module_name(), bookshop_get_module_name());
include_once(XOOPS_ROOT_PATH.'/footer.php');
?>