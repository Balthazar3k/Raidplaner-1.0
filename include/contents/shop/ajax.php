<?php 
#   Copyright by: Balthazar3k
#   Support: Balthazar3k.funpic.de


defined ('main') or die ( 'no direct access' );

$ajax = array();

switch ($menu->get(2)){
    case "pruchase":
        /* Get Data from article */
        $article = $core->db()->queryRow(standart_article_sql() . "
            WHERE a.article_id = ".$_POST['article_id']."
            LIMIT 1;
        ");
        
        /* Calc Price */
        $_POST['price'] = ($_POST['article_amount'] / $article['article_amount'])*$article['article_grossprice'];
        
        /* Add to Session [cart] */
        $_SESSION['shop']['cart'] = array_merge_recursive(
            $_SESSION['shop']['cart'],
            $core->func()->transformArray($_POST)
        );
        
    break;
}

$core->func()->ar($_SESSION['shop']);
?>