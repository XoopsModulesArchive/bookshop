<{* ********************************************************************************************** *}>
<{* ***************************************** WARNING ******************************************** *}>
<{* ********************************************************************************************** *}>
<{* ****** DON'T USE ANYHTING ELSE THAN JPEG OR PNG PICTURES OR THE PDF WILL NOT BE CREATED ****** *}>
<{* ****** This is a template for a PDF so you can't use all the html tags just this :      ****** *}>
<{* h1, h2, h3, h4, h5, h6, b, u, i, a, img, p, br, strong, em, font, blockquote, li, ul, ol, hr, td, th, tr, table, sup, sub, small *}>
<{* ********************************************************************************************** *}>
<{* ********************************************************************************************** *}>
<{* *************************************** PURCHASE ORDER **************************************** *}>
<p align='center'>
    <img src="<{$smarty.const.BOOKSHOP_IMAGES_URL}>pdf/purchase_order_header.jpg" alt="" border="0"/>
</p>
<table border="0">
    <tr>
        <td width="500"><b><{$smarty.const._BOOKSHOP_TITLE}></b></td>
        <td width="50" align="center"><b><{$smarty.const._BOOKSHOP_PRICE}></b></td>
        <td width="50"><b><{$smarty.const._BOOKSHOP_QUANTITY}></b></td>
        <td width="50"><b><{$smarty.const._BOOKSHOP_TOTAL}></b></td>
    </tr>
    <{foreach item=book from=$books}>
        <tr>
            <td width="500"><{$book.book_title|strip_tags|wordwrap:100|nl2br}></td>
            <td align="right" width="50"><{if $book.book_price > 0}><{$book.book_discount_price_ttc}><{else}><{$book.book_price_ttc}><{/if}> <{$mod_pref.money_full}></td>
            <td width="50" align="right">x</td>
            <td width="50">=</td>
        </tr>
    <{/foreach}>
</table>

<p align='center'>
    <img src="<{$smarty.const.BOOKSHOP_IMAGES_URL}>pdf/purchase_order_footer.jpg" alt="" border="0"/>
</p>
<{* ************************************** /PURCHASE ORDER **************************************** *}>
