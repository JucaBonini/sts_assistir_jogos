<?php
require_once('../../../wp-load.php');

$posts = get_posts(array(
    'post_type' => 'jogo',
    'numberposts' => -1
));

$misturas = array();

foreach ($posts as $p) {
    $esportes = wp_get_post_terms($p->ID, 'esporte');
    $campeonatos = wp_get_post_terms($p->ID, 'campeonato');
    
    $issue = false;
    $details = array();
    
    if (count($esportes) > 1) {
        $issue = true;
        $details[] = "Múltiplos Esportes: " . implode(', ', array_map(function($t){ return $t->name; }, $esportes));
    }
    
    if (count($campeonatos) > 1) {
        $issue = true;
        $details[] = "Múltiplos Campeonatos: " . implode(', ', array_map(function($t){ return $t->name; }, $campeonatos));
    }
    
    if ($issue) {
        $misturas[] = "ID: {$p->ID} | Title: {$p->post_title} | " . implode(' | ', $details);
    }
}

if (empty($misturas)) {
    echo "LIMPO: Não foram encontradas misturas. Cada jogo tem exatamente 1 esporte e 1 campeonato (ou zero).\n";
} else {
    echo "MISTURAS ENCONTRADAS:\n";
    foreach ($misturas as $m) {
        echo $m . "\n";
    }
}
